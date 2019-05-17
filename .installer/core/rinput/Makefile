
%.o: %.c 
	gcc -c -o $@ $< 

rinputd: rinput.c
	gcc -o $@ $^ 

install: 
	-service rinput stop
	chmod 755 rinputd res/rinput@init.d
	mkdir -p /etc/hive
	cp rinputd /etc/hive/rinputd
	cp res/rinput@init.d /etc/init.d/rinput
	mkdir -p /etc/hive/.config && chmod 705 /etc/hive/.config
	cp res/rinput.port /etc/hive/.config/rinput.port
	update-rc.d rinput defaults
	service rinput start
	service rinput status
