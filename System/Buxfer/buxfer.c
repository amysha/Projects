#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <sys/socket.h>
#include "lists.h"
#include "buxserver.h"
#include "wrapsock.h"

#define INPUT_BUFFER_SIZE 256
#define INPUT_ARG_MAX_NUM 5
#define DELIM " \n"

void Writen(int fd, void *ptr, size_t nbytes);
/* A standard template for error messages */
void error(const char *msg) {
    fprintf(stderr, "Error: %s\n", msg);
}

void broadcast(const char *s, int size, const char *user,
               Group *group, Client **client) {
    User member;
    int i;
    for (i = 0; i < MAXCLIENTS; i++) {
        for (member = group->users; member; member = member->next) {
            if (strcmp(client[i]->name, member->name) == 0 &&
                strcmp(client[i]->name, user) != 0) {
                Writen(client[i]->soc, &s, size);
            }
        }

    }
}

/* 
 * Read and process buxfer commands
 */
int process_args(int cmd_argc, char **cmd_argv,
                 Group **group_list_addr, Client *c, Client **client) {
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
            broadcast(s, strlen(s), c->name, g, client);
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

//int main(int argc, char* argv[]) {
//    char input[INPUT_BUFFER_SIZE];
//    char *cmd_argv[INPUT_ARG_MAX_NUM];
//    int cmd_argc;
//    FILE *input_stream;
//
//    /* Initialize the list head */
//    Group *group_list = NULL;
//
//    /* Batch mode */
//    if (argc == 2) {
//        input_stream = fopen(argv[1], "r");
//        if (input_stream == NULL) {
//            error("Error opening file");
//            exit(1);
//        }
//    }
//    /* Interactive mode */
//    else {
//        input_stream = stdin;
//    }
//    
//    printf("Welcome to Buxfer!\nPlease input command:\n>");
//    
//    while (fgets(input, INPUT_BUFFER_SIZE, input_stream) != NULL) {
//        /* Echo line if in batch mode */
//        if (argc == 2) {
//            printf("%s", input);
//        }
//        /* Tokenize arguments */
//        char *next_token = strtok(input, DELIM);
//        cmd_argc = 0;
//        while (next_token != NULL) {
//            if (cmd_argc >= INPUT_ARG_MAX_NUM - 1) {
//                error("Too many arguments!");
//                cmd_argc = 0;
//                break;
//            }
//            cmd_argv[cmd_argc] = next_token;
//            cmd_argc++;
//            next_token = strtok(NULL, DELIM);
//        }
//        cmd_argv[cmd_argc] = NULL;
//        if (cmd_argc > 0 && process_args(cmd_argc, cmd_argv, &group_list) == -1) {
//            break; /* quit command was entered */
//        }
//        printf(">");
//    }
//
//    /* Close file if in batch mode */
//    if (argc == 2) {
//        fclose(input_stream);
//    }
//    return 0;
//}
