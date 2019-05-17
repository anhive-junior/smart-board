#include <sys/types.h>
#include <sys/stat.h>
#include <stdio.h>
#include <errno.h>
#include <string.h>
#include <fcntl.h>
#include <signal.h>
#include <stdlib.h>
#include <memory.h>
#include <stdarg.h>

#include <time.h>
#include <unistd.h>
#include <wait.h>

#define die(str, args...) do { \
        perror(str); \
        exit(EXIT_FAILURE); \
    } while(0)
		
#define USE_DAEMON
#define MIN_SLEEP 60

////////////////////////////
//logfile control
int exists(const char *fname){
    FILE *file;
    if (file = fopen(fname, "r")) {
        fclose(file);
        return 1;
    }
    return 0;
}

char *path(char *fname) {
	char actualpath [256+1];
	char *ptr;
	ptr = realpath(fname, actualpath);
}

int file_size(char *fname) {
	
	struct stat st;
    if (stat(fname, &st) == -1) {
        perror("stat");
        return -1;
    }
	stat(fname, &st);
	return st.st_size;
}

int file_backup(char *fname, int size) {
	
	struct stat st;
	stat(fname, &st);
    if (stat(fname, &st) == -1) {
        perror("stat");
        return -1;
    }

	if (st.st_size < size) return 0;

	char buf[128];
	sprintf(buf, "cp %s %s.old && echo > %s", fname, fname, fname);
	system(buf);
	return 0;
}
 
void logwrite(char *logname, char *m) {
    FILE *f;
	int errnum;
    f = fopen(logname, "a");
    if (f == NULL)  {
		errnum = errno;
		fprintf(f, "Error opening %d (%s)\n", errnum, strerror(errnum));
		exit(EXIT_FAILURE);
	}
    fprintf(f, "%ld: %s\n", time(NULL), m);
	fclose(f);
}

void timeworklog(char *fmt, ...) {

	char result[256];
	
    va_list args;
    va_start(args, fmt);
    vsprintf(result, fmt, args);
#ifndef USE_DAEMON
	printf("%s\n", result);
#endif
    va_end(args);
	
#ifdef USE_DAEMON
	char *filename = "/dev/shm/timeworks.log";
	file_backup(filename, 2*1024*1024);
	logwrite(filename, result);
#endif
}

void joblog(char *jlog, char *fmt, ...) {

	char result[256];
	
    va_list args;
    va_start(args, fmt);
    vsprintf(result, fmt, args);
    va_end(args);
	
	file_backup(jlog, 2*1024*1024);
	logwrite(jlog, result);
}

int file_put_contents(char *fn, char *msg) {
	int fl;
	char *buf = NULL;

	if ((fl = open(fn, O_CREAT | O_WRONLY)) <= 0 ) {
		perror(fn);
		die("error: file writing is failed.");
	}
	
	write(fl, msg, strlen(msg));
	close(fl);
	
	return 1;
}
  
////////////////////////////
//schedule managements
struct task{
        int id;
        int interval;
        long begin;
        long end;
        long touch;
        int valid;  //1: OK, -1: CH, 0: NO
        char shell[80];
        struct task *next;
};

struct queue{
    struct task *first;
    struct task *cur;
    int size;
	long updated;
};

struct queue *load_task(char *filename){

    FILE *reads;
    reads=fopen(filename, "r");
    if (reads==NULL) {perror("Error");return NULL;}
	printf("file[%s] descriptor : %dp\n", filename, reads);
	
	int cnt=0;
	int cn=0;
	char id[16], interval[16], begin[16], end[16], shell[80];

	struct queue *que = NULL;
	que = (struct queue*)malloc(sizeof(struct queue));
	memset(que, 0x00, sizeof(struct queue));
	
	while(1) {
		if (!fscanf(reads,"%s %s %s %s %s", 
				id, begin, end, interval, shell) || feof(reads) ) break;
		
		//return 0;
		printf("ID:%s BEGIN:%s END:%s INT:%s SHELL:%s", id, begin, end, interval, shell);
		
		struct task *n= (struct task*)malloc(sizeof(struct task));
		memset(n, 0x00, sizeof(struct task));

		printf("clear task block size [%ld].\n", sizeof(struct task));
			
		n->id = atoi(id);
		n->interval = atoi(interval);
		n->begin = atol(begin);
		n->end = atol(end);
		n->touch = time(NULL);
		strcpy (n->shell, shell);

		timeworklog("after: %d %d %ld %ld %s", 
			n->id, n->interval, n->begin, n->end, n->shell);
		
		if (que->first == NULL) que->first = n;
		else que->cur->next=n;
		que->cur=n;
		que->size++;
	}
	que->updated = time(NULL);
	
	return que;
}

//new list update from old list when the task was existed
struct queue *reload_task(char *filename, struct queue *oque){

	//when task list was not newer the que info 
    struct stat attr;
    stat(filename, &attr);
    if (oque->updated >= attr.st_mtime) return oque;

	//when task list was updated after que setted
	struct queue *nque = load_task(filename);
	struct task *n = NULL;
	struct task *o = NULL;

	nque->cur = nque->first;
    while(	nque->cur ) {
		n = nque->cur;
		timeworklog("before: %d %d %ld %ld %s", 
			n->id, n->interval, n->begin, n->end, n->shell);

		//copy touch value to new if exist
		oque->cur = oque->first;
		while( oque->cur ) {
			o = oque->cur;
			//if not matched - nothing
			if ( strcmp (n->shell, o->shell) != 0) {
				oque->cur = o->next;
				continue;
			}
			
			//if matche - copy
			n->id       = o->id;
			n->interval = o->interval;
			n->begin    = o->begin;
			n->end      = o->end;
			n->touch    = o->touch;
			break;
		}
		timeworklog("after: %d %d %ld %ld %s", 
			n->id, n->interval, n->begin, n->end, n->shell);
		nque->cur = n->next;
	}
	free(oque);
	return nque;
}

char returnbuf[1024];
int run_task(struct task **t){
	
	timeworklog("task execute: %d i[%d] b[%d] f[%d] e[%d]  %s", 
			(*t)->id, (*t)->interval, (*t)->begin, 
			(*t)->touch, (*t)->end,  (*t)->shell); 

	if (!exists((*t)->shell)) {
		timeworklog("not exist [%s]",  (*t)->shell);
		return -1;
	}
			
	char logf[256];
	sprintf(logf, "%s.log", (*t)->shell);
	
	FILE *pp;
	pp = popen((*t)->shell, "r");
	joblog(logf, (*t)->shell);
	if (pp != NULL) {
		while (1) {
			char *line;
			line = fgets(returnbuf, sizeof(returnbuf), pp);
			if (line == NULL) break;
			
			//recording
			joblog(logf, line);
		}
		pclose(pp);
		timeworklog("success [%s]",  (*t)->shell);
	} else {
		timeworklog("fail [%s]",  (*t)->shell);
	}

	return 0;
}

int print_task(struct task *t){
	
	timeworklog("task info: %d i[%d] b[%d] f[%d] e[%d]  w[%d] %s", 
			t->id, t->interval, t->begin, 
			t->touch, t->end,  (int)time(NULL) - t->touch, t->shell); 

	return 0;
}

int main(int argc, char *argv[])
{
    int pid;
	char buf[254];
	struct queue *que;

	char *tasks = "/etc/hive/tasks/schedule.tasks";

	que = load_task(tasks);
	if (que == NULL){
        perror("task file error.");
        exit(0);
    }
 
#ifdef USE_DAEMON
    // fork 
    pid = fork();
    printf("pid = [%d] \n", pid);
 
    // log for fork error
    if(pid < 0){
        printf("fork error... : return is [%d] \n", pid );
        exit(0);
    // log for  parrent process kill
    }else if (pid > 0){
        printf("child process : [%d] - parent process : [%d] \n", pid, getpid());
        exit(0);
    // normal log
    }else if(pid == 0){
        printf("process : [%d]\n", getpid());
		sprintf(buf, "%d", getpid());
		file_put_contents("/run/timeworks.pid", buf);
    }
 
    // independant after closing terminal
    signal(SIGHUP, SIG_IGN);
    close(0);
    close(1);
    close(2);
 
    // change directory to root
    chdir("/");
 
    // assing new session id
    if ( setsid() == -1) {
        printf("fail to set session id ");
        exit(0);
    }
#endif
 
    int cnt = 0;
    printf("Start schdule");
 
    // insert the code to repeat
    while(1) {
        //resurrection for daemon killed
		struct task *t = NULL;
		int current = (int)time(NULL);
		int minsleep = MIN_SLEEP;
		int waiting = MIN_SLEEP;

		t = que->first;
		while ( t != NULL ) {
			print_task(t);
			if ( (t->begin < current) && (current < t->end)
			     && ((t->touch + t->interval) <= current) ) {
					 
#ifdef USE_DAEMON
				if((pid = fork()) < 0) {
					printf("fork error : restart daemon");
				}else if(pid == 0) {
					timeworklog ("pid is : %d", pid);
#endif
 					run_task(&t);
#ifdef USE_DAEMON
					exit(0);
					//break;
				}else if(pid > 0) {
					int ret; 
					wait(&ret); 
				}
#endif
 
				t->touch  = current;
				waiting = t->interval - (current - t->touch);
				timeworklog("sleep time is %d for active", waiting); 

			} else if ( (t->begin < current) && (current < t->end) ) {
				waiting = t->interval - (current - t->touch);
				timeworklog("sleep time is %d for sleeping", waiting);
			} else {
				waiting = t->begin - current;
				if (waiting < 1) waiting = 60;
				timeworklog("sleep time is %d for none active", waiting); 
			}
			minsleep = minsleep < waiting ? minsleep : waiting;
			t = t->next;
		}
		timeworklog("sleep [%d] for next.", minsleep); 
        sleep(minsleep);
		cnt++;
		que = reload_task(tasks, que);
    }
}
