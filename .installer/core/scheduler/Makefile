
%.o: %.c 
	gcc -c -o $@ $< 

timeworks: timeworks.c
	gcc -o $@ $^ 

install: 
	-service timeworks stop
	chmod 755 timeworks res/timeworks@init.d
	cp timeworks /etc/hive/timeworks
	cp res/timeworks@init.d /etc/init.d/timeworks
	update-rc.d timeworks defaults
	mkdir -p /etc/hive/tasks
	touch /etc/hive/tasks/schedule.tasks
	chown www-data:www-data /etc/hive/tasks/schedule.tasks
	service timeworks start
	service timeworks status

packing:
	 tar -cvf www/timework.core.tar.gz res/* tasks/* timeworks timeworks.c Makefile
