
%.o: %.c 
	gcc -c -o $@ $< 

broadcastd: broadcast.c
	gcc -o $@ $^ 

install: 
	-service broadcast stop
	chmod 755 broadcastd res/broadcast@init.d
	cp broadcastd /etc/hive/broadcastd
	cp res/broadcast@init.d /etc/init.d/broadcast
	update-rc.d broadcast defaults
	service broadcast start
	service broadcast status
