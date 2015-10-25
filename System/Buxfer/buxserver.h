#ifndef BUXSERVER_H
#define BUXSERVER_H

#define MAXLINE 256
#define MAXGROUP 100
#define MAXCLIENTS 30

typedef struct {
    char name[MAXLINE];
    //Group group[MAXGROUP];
	int soc;
	char buf[MAXLINE];
	int curpos;
} Client;

int process_args(int cmd_argc, char **cmd_argv,
                 Group **group_list_addr, Client *c);
ssize_t Readn(int fd, void *ptr, size_t nbytes);
void Writen(int fd, void *ptr, size_t nbytes);

#endif