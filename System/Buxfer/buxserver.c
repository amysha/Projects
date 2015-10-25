#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <strings.h>
#include <unistd.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>    /* Internet domain header */
#include "wrapsock.h"
#include "lists.h"
#include "buxserver.h"

#define MAXLINE 256
#define MAXCLIENTS 30
#define LISTENQ 30
#define INPUT_ARG_MAX_NUM 4
#define DELIM " \n"

#ifndef PORT
#define PORT 56715
#endif

#define INPUT_BUFFER_SIZE 256
Client client[MAXCLIENTS];

void Writen(int fd, void *ptr, size_t nbytes);
/* A standard template for error messages */
void error(const char *msg) {
    fprintf(stderr, "Error: %s\n", msg);
}

void broadcast(const char *s, int size, const char *user, Group *group) {
    User *member;
    int i;
    for (i = 0; i < MAXCLIENTS; i++) {
        for (member = group->users; member; member = member->next) {
            if (strcmp(client[i].name, member->name) == 0 &&
                strcmp(client[i].name, user) != 0) {
                Writen(client[i].soc, &s, size);
            }
        }
        
    }
}

/*
 * Read and process buxfer commands
 */
int process_args(int cmd_argc, char **cmd_argv,
                 Group **group_list_addr, Client *c) {
    Group *group_list = *group_list_addr;
    Group *g;
    char *buf;
    
    if (cmd_argc <= 0) {
        Writen(c->soc, "Error: Incorrect syntax\r\n", 25);
        
    } else if (strcmp(cmd_argv[0], "quit") == 0 && cmd_argc == 1) {
        return -1;
        
    } else if (strcmp(cmd_argv[0], "add_group") == 0 && cmd_argc == 2) {
        Group **group = group_list_addr;
        buf = add_group(group_list_addr, cmd_argv[1]);
        Writen(c->soc, buf, strlen(buf));
        g = find_group(*group, cmd_argv[1]);
        buf = add_user(g, c->name);
        Writen(c->soc, buf, strlen(buf));
        if (strchr(buf, atoi("Successfully"))) {
            char s[27 + strlen(c->name) + strlen(cmd_argv[1]) + 1];
            snprintf(s, 27 + strlen(c->name) + strlen(cmd_argv[1]) + 1,
                     "%s has been added to group %s\r\n", c->name, cmd_argv[1]);
            broadcast(s, strlen(s), c->name, g);
        }
        
    } else if (strcmp(cmd_argv[0], "list_groups") == 0 && cmd_argc == 1) {
        buf = list_groups(group_list);
        Writen(c->soc, buf, strlen(buf));
        
    } else if (strcmp(cmd_argv[0], "list_users") == 0 && cmd_argc == 2) {
        if ((g = find_group(group_list, cmd_argv[1])) == NULL) {
            Writen(c->soc, "\0", 1);
        } else {
            buf = list_users(g);
            Writen(c->soc, buf, strlen(buf));
        }
        
    } else if (strcmp(cmd_argv[0], "user_balance") == 0 && cmd_argc == 2) {
        if ((g = find_group(group_list, cmd_argv[1])) == NULL) {
            Writen(c->soc, "\0", 1);
        } else {
            buf = user_balance(g, c->name);
            Writen(c->soc, buf, strlen(buf));
        }
        
    } else if (strcmp(cmd_argv[0], "add_xct") == 0 && cmd_argc == 3) {
        
        if ((g = find_group(group_list, cmd_argv[1])) == NULL) {
            Writen(c->soc, "\0", 1);
        } else {
            char *end;
            double amount = strtof(cmd_argv[2], &end);
            if (end == cmd_argv[2]) {
                Writen(c->soc, "Error: Incorrect number format\r\n", 32);
            } else {
                buf = add_xct(g, c->name, amount);
                Writen(c->soc, buf, strlen(buf));
            }
        }
        
    } else {
        Writen(c->soc, "Error: Incorrect syntax\r\n", 25);
    }
    return 0;
}

/*  read from the client
 * 	returns -1 if the socket needs to be closed and 0 otherwise */
int readfromclient(Client *c, Group **group_list) {
	
    printf("hi\n");
    char *cmd_argv[INPUT_ARG_MAX_NUM];
    int cmd_argc;
	char *startptr = &c->buf[c->curpos];
	int len = read(c->soc, startptr, MAXLINE - c->curpos);
	if(len <= 0) {
		if(len == -1) {
			perror("read on socket");
		}
		return -1;
		/* connection closed by client */
        
	} else {
		c->curpos += len;
		c->buf[c->curpos] = '\0';
        
		/* Did we get a whole line?*/
		if (strchr(c->buf, '\n') || strchr(c->buf, '\r')) {
            
            char *next_token = strtok(c->buf, DELIM);
            cmd_argc = 0;
            while (next_token != NULL) {
                if (cmd_argc >= INPUT_ARG_MAX_NUM - 1) {
                    Writen(c->soc, "Too many arguments!\r\n", 21);
                    cmd_argc = 0;
                    break;
                }
                cmd_argv[cmd_argc] = next_token;
                cmd_argc++;
                next_token = strtok(NULL, DELIM);
            }
            cmd_argv[cmd_argc] = NULL;
            if (cmd_argc > 0 &&
                process_args(cmd_argc, cmd_argv, group_list, c) == -1) {
                /* quit command was entered */
                Writen(c->soc, "Goodbye!\r\n", 10); 
                return -1;
            }
                
			// Need to shift anything still in the buffer over
			// to beginning.
			char *leftover = &c->buf[c->curpos];
			memmove(c->buf, leftover, c->curpos);
			c->curpos = 0;
			return 0;
		} else {
			/*Don't do anything. Wait for more input. */
			return 0;
		}
        //return 0;
	}
}


int main(int argc, char **argv) {
	int i, maxi, maxfd, listenfd, connfd;
	int nready;
	fd_set  rset, allset;
	socklen_t clilen;
	struct sockaddr_in cliaddr, servaddr;
	int yes = 1;
    Group *group_list = NULL;
    
	listenfd = Socket(AF_INET, SOCK_STREAM, 0);
    
	bzero(&servaddr, sizeof(servaddr));
	servaddr.sin_family      = AF_INET;
	servaddr.sin_addr.s_addr = htonl(INADDR_ANY);
	servaddr.sin_port        = htons(PORT);
    
	if((setsockopt(listenfd, SOL_SOCKET, SO_REUSEADDR, &yes,
                   sizeof(int))) == -1) {
		perror("setsockopt");
	}
    
	Bind(listenfd, (struct sockaddr *) &servaddr, sizeof(servaddr));
	Listen(listenfd, LISTENQ);
    
	maxfd = listenfd;   /* initialize */
	maxi = -1;      /* index into client[] array */
	for (i = 0; i < MAXCLIENTS; i++) {
		client[i].soc = -1; /* -1 indicates available entry */
		client[i].curpos = 0;
	}
    
	FD_ZERO(&allset);
	FD_SET(listenfd, &allset);
    
	for ( ; ; ) {
		rset = allset;      /* structure assignment */
		nready = Select(maxfd+1, &rset, NULL, NULL, NULL);
        
        char buf[MAXLINE];
        int num;
        int s;
		if (FD_ISSET(listenfd, &rset)) {    /* new client connection */
			clilen = sizeof(cliaddr);
			connfd = Accept(listenfd, (struct sockaddr *) &cliaddr, &clilen);
			printf("accepted a new client\n");
            
			for (i = 0; i < MAXCLIENTS; i++)
                if (client[i].soc < 0) {
                    client[i].soc = connfd; /* save descriptor */
                    if(write(client[i].soc, "What is your name? ", 20) == -1) {
                        perror("write");
                        exit(1);
                    }
                    while ((num = read(client[i].soc, buf, MAXLINE)) > 0 &&
                           (buf[0] == '\n' || buf[0] == '\r' ||
                            buf[0] == ' ')) {
                               if(write(client[i].soc, "What is your name? ", 20)
                                  == -1) {
                                   perror("write");
                                   exit(1);
                               }
                           }
                    if (num < 0) {
                        perror("read");
                        exit(1);
                    }
                    if (strchr(buf, '\n') && strchr(buf, '\r')) {
                        s = num - 2;
                        buf[s] = '\0';
                    } else {
                        s = num - 1;
                        buf[s] = '\0';
                    }
                    
                    snprintf(client[i].name, s + 1, "%s", buf);
                    char welcome[42 + s + 1];
                    snprintf(welcome, 42 + s + 1,
                             "Welcome, %s! Please enter Buxfer commands\r\n",
                             client[i].name);
                    if(write(client[i].soc, welcome, strlen(welcome)) == -1) {
                        perror("write");
                        exit(1);
                    }
                    break;
                }
			if (i == MAXCLIENTS)
				printf("too many clients");
            
			FD_SET(connfd, &allset);    /* add new descriptor to set */
			if (connfd > maxfd)
				maxfd = connfd; /* for select */
			if (i > maxi)
				maxi = i;   /* max index in client[] array */
            
			if (--nready <= 0)
				continue;   /* no more readable descriptors */
		}
        
		for (i = 0; i <= maxi; i++) {   /* check all clients for data */
			if ( client[i].soc < 0)
				continue;
			if (FD_ISSET(client[i].soc, &rset)) {
				int result = readfromclient(&client[i], &group_list);
				
				if(result == -1)  {
					Close(client[i].soc);
					FD_CLR(client[i].soc, &allset);
					client[i].soc = -1;
				}
				if (--nready <= 0)
					break;  /* no more readable descriptors */
			}
		}
	}
}
