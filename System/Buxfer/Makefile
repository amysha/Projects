CC = gcc
PORT=56716
CFLAGS = -DEPORT=$(PORT) -g -Wall

buxserver: buxserver.o wrapsock.o writen.o readn.o lists.o
	${CC} ${CFLAGS} -o $@ buxserver.o wrapsock.o writen.o readn.o lists.o

.c.o:
	${CC} ${CFLAGS}  -c $<

clean: 
	-rm buxserver *.o
