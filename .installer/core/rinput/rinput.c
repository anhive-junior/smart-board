#include <stdio.h>
#include <stdlib.h>
#include <string.h>
#include <unistd.h>
#include <fcntl.h>
#include <errno.h>
#include <linux/input.h>
#include <linux/uinput.h>


#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/types.h>
#include <time.h> 

#include <sys/stat.h>
#include <signal.h>
#include <memory.h>
#include <stdarg.h>
#include <sys/wait.h>
#include <wait.h>

#define die(str, args...) do { \
        perror(str); \
        exit(EXIT_FAILURE); \
    } while(0)

		
char logfile[254];

int file_backup(char *fname, int size) {
	
	struct stat st;
	stat(fname, &st);
    if (stat(fname, &st) == -1) {
        perror("stat");
        return -1;
    }

	if (st.st_size < size) return 0;

	char buf[512];
	sprintf(buf, "cp %s %s.old && echo > %s", fname, fname, fname);
	system(buf);
	return 0;
}

void rinput_log(char *fmt, ...) {
	char *jlog = "/run/shm/rinput.log";
    char result[256];
	
	file_backup(jlog, 2*1024*1024);
	
    va_list args;
    va_start(args, fmt);
    vsprintf(result, fmt, args);
    va_end(args);

    FILE *f;
    f = fopen(jlog, "a+");
    if (f == NULL) { /* Something is wrong   */}
    fprintf(f, "%d: %s\n", (int)time(NULL), result);
	fclose(f);
}

char *file_get_contents(char *fn) {
	int fl;
	char *buf = NULL;
	
	struct stat st;
	stat(fn, &st);
	int size = st.st_size;

	printf("%d : size of file", size);

	if ((fl = open(fn, O_RDONLY)) <= 0 ) return NULL;
	
	buf = (char *)malloc((size+1)*sizeof(char));

	read(fl, buf, size);
	close(fl);
	*(buf+size+1)==0x00;
	
	return buf;
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

char *exec(char *ex) {
	rinput_log( "exec: %s", ex);
	
	FILE *fp;
	char *out;
	char *temp;
	
	char cmd[256];
	sprintf(cmd, "lxterminal -e %s", ex);

	int buf_size = 1035;
	int out_size = buf_size;
	/* Open the command for reading. */
	fp = popen(cmd, "r");
	if (fp == NULL) {
		rinput_log( "Failed to run command\n");
		printf("Failed to run command\n" );
		return NULL;
	}
	
	rinput_log( "Success popen\n");

	out = (char *) malloc (out_size);
	memset(out, 0x00, sizeof(out));
	temp = (char *) malloc (buf_size);
	memset(temp, 0x00, buf_size);
	/* Read the output a line at a time - output it. */
	while (fgets(temp, sizeof(temp)-1, fp) != NULL) {
		if (strlen(temp)+strlen(out) > out_size ) {
			out = (char *)realloc(out, out_size+buf_size);
			out_size += buf_size;
		}
		sprintf(&out[strlen(out)], "%s", temp);
		rinput_log( "  -x  [%s]\n", temp);
	}

	/* close */
	pclose(fp);
	free(temp);
	
	return out;
}
		
void ioevent(int fd, int type, int code, int value) {
    struct input_event     ev;
	memset(&ev, 0, sizeof(struct input_event));
	ev.type = type;
	ev.code = code;
	ev.value = value;
	if(write(fd, &ev, sizeof(struct input_event)) < 0) die("error: write");
}	

void mousemove(int fd, int type, int value_x, int value_y ) {
	if (type == EV_REL ) {
		ioevent(fd, type, REL_X, value_x);
		ioevent(fd, type, REL_Y, value_y);
	} else if (type == EV_ABS ) {
		ioevent(fd, type, ABS_X, value_x);
		ioevent(fd, type, ABS_Y, value_y);
	} else die("error: ioctl type of mouse");
	ioevent(fd, EV_SYN, SYN_REPORT, 0);
}

void toggled(int fd, int type, int btn, int repeat ) {
	int i=0;
	for (i=0; i <repeat; i++) {
		ioevent(fd, type, btn, 1);
		ioevent(fd, EV_SYN, SYN_REPORT, 0);
		usleep(2000);
		ioevent(fd, type, btn, 0);
		ioevent(fd, EV_SYN, SYN_REPORT, 0);
		usleep(2000);
	}
	return;
}

void ascii_toggled(int fd, int type, char ch, int repeat) {

	char *lower = "1234567890-=qwertyuiop[]asdfghjkl;'`\\zxcvbnm,./";
	char *upper1 = "QWERTYUIOPASDFGHJKLZXCVBNM";
	char *upper2 = "!@#$%^&*()_+{}:\"?><";
	
	int i;
	int btn;
	char lch;
	int uppercase;
	lch = ((65 <= ch) && (ch <= 90)) ? ch + 32 : ch;
	uppercase = ((65 <= ch) && (ch <= 90)) ? 1 : 0;
	
     btn =(lch ==(char)'1') ? KEY_1:
          (lch ==(char)'2') ? KEY_2:
          (lch ==(char)'3') ? KEY_3:
          (lch ==(char)'4') ? KEY_4:
          (lch ==(char)'5') ? KEY_5:
          (lch ==(char)'6') ? KEY_6:
          (lch ==(char)'7') ? KEY_7:
          (lch ==(char)'8') ? KEY_8:
          (lch ==(char)'9') ? KEY_9:
          (lch ==(char)'0') ? KEY_0:
          (lch ==(char)'-') ? KEY_MINUS:
          (lch ==(char)'=') ? KEY_EQUAL:
          (lch ==(char)'q') ? KEY_Q:
          (lch ==(char)'w') ? KEY_W:
          (lch ==(char)'e') ? KEY_E:
          (lch ==(char)'r') ? KEY_R:
          (lch ==(char)'t') ? KEY_T:
          (lch ==(char)'y') ? KEY_Y:
          (lch ==(char)'u') ? KEY_U:
          (lch ==(char)'i') ? KEY_I:
          (lch ==(char)'o') ? KEY_O:
          (lch ==(char)'p') ? KEY_P:
          (lch ==(char)'[') ? KEY_LEFTBRACE:
          (lch ==(char)']') ? KEY_RIGHTBRACE:
          (lch ==(char)'a') ? KEY_A:
          (lch ==(char)'s') ? KEY_S:
          (lch ==(char)'d') ? KEY_D:
          (lch ==(char)'f') ? KEY_F:
          (lch ==(char)'g') ? KEY_G:
          (lch ==(char)'h') ? KEY_H:
          (lch ==(char)'j') ? KEY_J:
          (lch ==(char)'k') ? KEY_K:
          (lch ==(char)'l') ? KEY_L:
          (lch ==(char)';') ? KEY_SEMICOLON:
          (lch ==(char)'\'') ? KEY_APOSTROPHE:
          (lch ==(char)'`') ? KEY_GRAVE:
          (lch ==(char)'\\') ? KEY_BACKSLASH:
          (lch ==(char)'z') ? KEY_Z:
          (lch ==(char)'x') ? KEY_X:
          (lch ==(char)'c') ? KEY_C:
          (lch ==(char)'v') ? KEY_V:
          (lch ==(char)'b') ? KEY_B:
          (lch ==(char)'n') ? KEY_N:
          (lch ==(char)'m') ? KEY_M:
          (lch ==(char)',') ? KEY_COMMA:
          (lch ==(char)'.') ? KEY_DOT:
          (lch ==(char)'/') ? KEY_SLASH:
          (lch ==(char)' ') ? KEY_SPACE:
		  -1;

	//upper case for specific characters
	if (btn == -1) {
		btn =
		  (lch ==(char)'!') ? KEY_1:
		  (lch ==(char)'@') ? KEY_2:
		  (lch ==(char)'#') ? KEY_3:
		  (lch ==(char)'$') ? KEY_4:
		  (lch ==(char)'%') ? KEY_5:
		  (lch ==(char)'^') ? KEY_6:
		  (lch ==(char)'&') ? KEY_7:
		  (lch ==(char)'*') ? KEY_8:
		  (lch ==(char)'(') ? KEY_9:
		  (lch ==(char)')') ? KEY_0:
		  (lch ==(char)'_') ? KEY_MINUS:
		  (lch ==(char)'+') ? KEY_EQUAL:
          (lch ==(char)'{') ? KEY_LEFTBRACE:
          (lch ==(char)'}') ? KEY_RIGHTBRACE:
          (lch ==(char)':') ? KEY_SEMICOLON:
          (lch ==(char)'"') ? KEY_APOSTROPHE:
          (lch ==(char)'~') ? KEY_GRAVE:
          (lch ==(char)'|') ? KEY_BACKSLASH:
          (lch ==(char)'<') ? KEY_COMMA:
          (lch ==(char)'>') ? KEY_DOT:
          (lch ==(char)'?') ? KEY_SLASH:
		  -1;
		if (btn > 0) uppercase = 1;
	}

	//no matchs
	if (btn < 0) return;

	for (i=0; i <repeat; i++) {
		
		if(uppercase) {
			ioevent(fd, EV_KEY, KEY_LEFTSHIFT, 1);
			ioevent(fd, EV_SYN, SYN_REPORT, 0);
			usleep(2000);
			ioevent(fd, EV_KEY, KEY_LEFTSHIFT, 2);
			ioevent(fd, EV_SYN, SYN_REPORT, 0);
			usleep(2000);
		}
		ioevent(fd, type, btn, 1);
		ioevent(fd, EV_SYN, SYN_REPORT, 0);
		usleep(2000);
		ioevent(fd, type, btn, 0);
		ioevent(fd, EV_SYN, SYN_REPORT, 0);
		usleep(2000);
		if(uppercase) {
			ioevent(fd, EV_KEY, KEY_LEFTSHIFT, 0);
			ioevent(fd, EV_SYN, SYN_REPORT, 0);
			usleep(2000);
		}
	}
	
	//toggled(fd, type, btn, repeat );
	return;
}

void ioctl_toggled(int fd, int type, char io, int repeat) {
	int i;

	for (i=0; i <repeat; i++) {
		ioevent(fd, type, io, 1);
		ioevent(fd, EV_SYN, SYN_REPORT, 0);
		usleep(2000);
		ioevent(fd, type, io, 0);
		ioevent(fd, EV_SYN, SYN_REPORT, 0);
		usleep(2000);
	}
	
	return;
}

char *iokey[256];

void init_iokey() {
	
	//if (iokey[0 ] != NULL) return;
	memset(iokey, 0x00, sizeof(iokey));
	
	iokey[KEY_RESERVED        ] = "[RESERVED]";
	iokey[KEY_ESC             ] = "[ESC]";
	iokey[KEY_1               ] = "[1]";
	iokey[KEY_2               ] = "[2]";
	iokey[KEY_3               ] = "[3]";
	iokey[KEY_4               ] = "[4]";
	iokey[KEY_5               ] = "[5]";
	iokey[KEY_6               ] = "[6]";
	iokey[KEY_7               ] = "[7]";
	iokey[KEY_8               ] = "[8]";
	iokey[KEY_9               ] = "[9]";
	iokey[KEY_0               ] = "[0]";
	iokey[KEY_MINUS           ] = "[MINUS]";
	iokey[KEY_EQUAL           ] = "[EQUAL]";
	iokey[KEY_BACKSPACE       ] = "[BACKSPACE]";
	iokey[KEY_TAB             ] = "[TAB]";
	iokey[KEY_Q               ] = "[Q]";
	iokey[KEY_W               ] = "[W]";
	iokey[KEY_E               ] = "[E]";
	iokey[KEY_R               ] = "[R]";
	iokey[KEY_T               ] = "[T]";
	iokey[KEY_Y               ] = "[Y]";
	iokey[KEY_U               ] = "[U]";
	iokey[KEY_I               ] = "[I]";
	iokey[KEY_O               ] = "[O]";
	iokey[KEY_P               ] = "[P]";
	iokey[KEY_LEFTBRACE       ] = "[LEFTBRACE]";
	iokey[KEY_RIGHTBRACE      ] = "[RIGHTBRACE]";
	iokey[KEY_ENTER           ] = "[ENTER]";
	iokey[KEY_LEFTCTRL        ] = "[LEFTCTRL]";
	iokey[KEY_A               ] = "[A]";
	iokey[KEY_S               ] = "[S]";
	iokey[KEY_D               ] = "[D]";
	iokey[KEY_F               ] = "[F]";
	iokey[KEY_G               ] = "[G]";
	iokey[KEY_H               ] = "[H]";
	iokey[KEY_J               ] = "[J]";
	iokey[KEY_K               ] = "[K]";
	iokey[KEY_L               ] = "[L]";
	iokey[KEY_SEMICOLON       ] = "[SEMICOLON]";
	iokey[KEY_APOSTROPHE      ] = "[APOSTROPHE]";
	iokey[KEY_GRAVE           ] = "[GRAVE]";
	iokey[KEY_LEFTSHIFT       ] = "[LEFTSHIFT]";
	iokey[KEY_BACKSLASH       ] = "[BACKSLASH]";
	iokey[KEY_Z               ] = "[Z]";
	iokey[KEY_X               ] = "[X]";
	iokey[KEY_C               ] = "[C]";
	iokey[KEY_V               ] = "[V]";
	iokey[KEY_B               ] = "[B]";
	iokey[KEY_N               ] = "[N]";
	iokey[KEY_M               ] = "[M]";
	iokey[KEY_COMMA           ] = "[COMMA]";
	iokey[KEY_DOT             ] = "[DOT]";
	iokey[KEY_SLASH           ] = "[SLASH]";
	iokey[KEY_RIGHTSHIFT      ] = "[RIGHTSHIFT]";
	iokey[KEY_KPASTERISK      ] = "[KPASTERISK]";
	iokey[KEY_LEFTALT         ] = "[LEFTALT]";
	iokey[KEY_SPACE           ] = "[SPACE]";
	iokey[KEY_CAPSLOCK        ] = "[CAPSLOCK]";
	iokey[KEY_F1              ] = "[F1]";
	iokey[KEY_F2              ] = "[F2]";
	iokey[KEY_F3              ] = "[F3]";
	iokey[KEY_F4              ] = "[F4]";
	iokey[KEY_F5              ] = "[F5]";
	iokey[KEY_F6              ] = "[F6]";
	iokey[KEY_F7              ] = "[F7]";
	iokey[KEY_F8              ] = "[F8]";
	iokey[KEY_F9              ] = "[F9]";
	iokey[KEY_F10             ] = "[F10]";
	iokey[KEY_NUMLOCK         ] = "[NUMLOCK]";
	iokey[KEY_SCROLLLOCK      ] = "[SCROLLLOCK]";
	iokey[KEY_KP7             ] = "[KP7]";
	iokey[KEY_KP8             ] = "[KP8]";
	iokey[KEY_KP9             ] = "[KP9]";
	iokey[KEY_KPMINUS         ] = "[KPMINUS]";
	iokey[KEY_KP4             ] = "[KP4]";
	iokey[KEY_KP5             ] = "[KP5]";
	iokey[KEY_KP6             ] = "[KP6]";
	iokey[KEY_KPPLUS          ] = "[KPPLUS]";
	iokey[KEY_KP1             ] = "[KP1]";
	iokey[KEY_KP2             ] = "[KP2]";
	iokey[KEY_KP3             ] = "[KP3]";
	iokey[KEY_KP0             ] = "[KP0]";
	iokey[KEY_KPDOT           ] = "[KPDOT]";
	iokey[KEY_ZENKAKUHANKAKU  ] = "[ZENKAKUHANKAKU]";
	iokey[KEY_102ND           ] = "[102ND]";
	iokey[KEY_F11             ] = "[F11]";
	iokey[KEY_F12             ] = "[F12]";
	iokey[KEY_RO              ] = "[RO]";
	iokey[KEY_KATAKANA        ] = "[KATAKANA]";
	iokey[KEY_HIRAGANA        ] = "[HIRAGANA]";
	iokey[KEY_HENKAN          ] = "[HENKAN]";
	iokey[KEY_KATAKANAHIRAGANA] = "[KATAKANAHIRAGANA]";
	iokey[KEY_MUHENKAN        ] = "[MUHENKAN]";
	iokey[KEY_KPJPCOMMA       ] = "[KPJPCOMMA]";
	iokey[KEY_KPENTER         ] = "[KPENTER]";
	iokey[KEY_RIGHTCTRL       ] = "[RIGHTCTRL]";
	iokey[KEY_KPSLASH         ] = "[KPSLASH]";
	iokey[KEY_SYSRQ           ] = "[SYSRQ]";
	iokey[KEY_RIGHTALT        ] = "[RIGHTALT]";
	iokey[KEY_LINEFEED        ] = "[LINEFEED]";
	iokey[KEY_HOME            ] = "[HOME]";
	iokey[KEY_UP              ] = "[UP]";
	iokey[KEY_PAGEUP          ] = "[PAGEUP]";
	iokey[KEY_LEFT            ] = "[LEFT]";
	iokey[KEY_RIGHT           ] = "[RIGHT]";
	iokey[KEY_END             ] = "[END]";
	iokey[KEY_DOWN            ] = "[DOWN]";
	iokey[KEY_PAGEDOWN        ] = "[PAGEDOWN]";
	iokey[KEY_INSERT          ] = "[INSERT]";
	iokey[KEY_DELETE          ] = "[DELETE]";
	iokey[KEY_MACRO           ] = "[MACRO]";
	iokey[KEY_MUTE            ] = "[MUTE]";
	iokey[KEY_VOLUMEDOWN      ] = "[VOLUMEDOWN]";
	iokey[KEY_VOLUMEUP        ] = "[VOLUMEUP]";
	iokey[KEY_POWER           ] = "[POWER]";
	iokey[KEY_KPEQUAL         ] = "[KPEQUAL]";
	iokey[KEY_KPPLUSMINUS     ] = "[KPPLUSMINUS]";
	iokey[KEY_PAUSE           ] = "[PAUSE]";
	iokey[KEY_SCALE           ] = "[SCALE]";
	iokey[KEY_KPCOMMA         ] = "[KPCOMMA]";
	iokey[KEY_HANGEUL         ] = "[HANGEUL]";
	iokey[KEY_HANGUEL         ] = "[HANGUEL]";
	iokey[KEY_HANJA           ] = "[HANJA]";
	iokey[KEY_YEN             ] = "[YEN]";
	iokey[KEY_LEFTMETA        ] = "[LEFTMETA]";
	iokey[KEY_RIGHTMETA       ] = "[RIGHTMETA]";
	iokey[KEY_COMPOSE         ] = "[COMPOSE]";
	iokey[KEY_STOP            ] = "[STOP]";
	iokey[KEY_AGAIN           ] = "[AGAIN]";
	iokey[KEY_PROPS           ] = "[PROPS]";
	iokey[KEY_UNDO            ] = "[UNDO]";
	iokey[KEY_FRONT           ] = "[FRONT]";
	iokey[KEY_COPY            ] = "[COPY]";
	iokey[KEY_OPEN            ] = "[OPEN]";
	iokey[KEY_PASTE           ] = "[PASTE]";
	iokey[KEY_FIND            ] = "[FIND]";
	iokey[KEY_CUT             ] = "[CUT]";
	iokey[KEY_HELP            ] = "[HELP]";
	iokey[KEY_MENU            ] = "[MENU]";
	iokey[KEY_CALC            ] = "[CALC]";
	iokey[KEY_SETUP           ] = "[SETUP]";
	iokey[KEY_SLEEP           ] = "[SLEEP]";
	iokey[KEY_WAKEUP          ] = "[WAKEUP]";
	iokey[KEY_FILE            ] = "[FILE]";
	iokey[KEY_SENDFILE        ] = "[SENDFILE]";
	iokey[KEY_DELETEFILE      ] = "[DELETEFILE]";
	iokey[KEY_XFER            ] = "[XFER]";
	iokey[KEY_PROG1           ] = "[PROG1]";
	iokey[KEY_PROG2           ] = "[PROG2]";
	iokey[KEY_WWW             ] = "[WWW]";
	iokey[KEY_MSDOS           ] = "[MSDOS]";
	iokey[KEY_COFFEE          ] = "[COFFEE]";
	iokey[KEY_SCREENLOCK      ] = "[SCREENLOCK]";
	iokey[KEY_DIRECTION       ] = "[DIRECTION]";
	iokey[KEY_CYCLEWINDOWS    ] = "[CYCLEWINDOWS]";
	iokey[KEY_MAIL            ] = "[MAIL]";
	iokey[KEY_BOOKMARKS       ] = "[BOOKMARKS]";
	iokey[KEY_COMPUTER        ] = "[COMPUTER]";
	iokey[KEY_BACK            ] = "[BACK]";
	iokey[KEY_FORWARD         ] = "[FORWARD]";
	iokey[KEY_CLOSECD         ] = "[CLOSECD]";
	iokey[KEY_EJECTCD         ] = "[EJECTCD]";
	iokey[KEY_EJECTCLOSECD    ] = "[EJECTCLOSECD]";
	iokey[KEY_NEXTSONG        ] = "[NEXTSONG]";
	iokey[KEY_PLAYPAUSE       ] = "[PLAYPAUSE]";
	iokey[KEY_PREVIOUSSONG    ] = "[PREVIOUSSONG]";
	iokey[KEY_STOPCD          ] = "[STOPCD]";
	iokey[KEY_RECORD          ] = "[RECORD]";
	iokey[KEY_REWIND          ] = "[REWIND]";
	iokey[KEY_PHONE           ] = "[PHONE]";
	iokey[KEY_ISO             ] = "[ISO]";
	iokey[KEY_CONFIG          ] = "[CONFIG]";
	iokey[KEY_HOMEPAGE        ] = "[HOMEPAGE]";
	iokey[KEY_REFRESH         ] = "[REFRESH]";
	iokey[KEY_EXIT            ] = "[EXIT]";
	iokey[KEY_MOVE            ] = "[MOVE]";
	iokey[KEY_EDIT            ] = "[EDIT]";
	iokey[KEY_SCROLLUP        ] = "[SCROLLUP]";
	iokey[KEY_SCROLLDOWN      ] = "[SCROLLDOWN]";
	iokey[KEY_KPLEFTPAREN     ] = "[KPLEFTPAREN]";
	iokey[KEY_KPRIGHTPAREN    ] = "[KPRIGHTPAREN]";
	iokey[KEY_NEW             ] = "[NEW]";
	iokey[KEY_REDO            ] = "[REDO]";
	iokey[KEY_F13             ] = "[F13]";
	iokey[KEY_F14             ] = "[F14]";
	iokey[KEY_F15             ] = "[F15]";
	iokey[KEY_F16             ] = "[F16]";
	iokey[KEY_F17             ] = "[F17]";
	iokey[KEY_F18             ] = "[F18]";
	iokey[KEY_F19             ] = "[F19]";
	iokey[KEY_F20             ] = "[F20]";
	iokey[KEY_F21             ] = "[F21]";
	iokey[KEY_F22             ] = "[F22]";
	iokey[KEY_F23             ] = "[F23]";
	iokey[KEY_F24             ] = "[F24]";
	iokey[KEY_PLAYCD          ] = "[PLAYCD]";
	iokey[KEY_PAUSECD         ] = "[PAUSECD]";
	iokey[KEY_PROG3           ] = "[PROG3]";
	iokey[KEY_PROG4           ] = "[PROG4]";
	iokey[KEY_DASHBOARD       ] = "[DASHBOARD]";
	iokey[KEY_SUSPEND         ] = "[SUSPEND]";
	iokey[KEY_CLOSE           ] = "[CLOSE]";
	iokey[KEY_PLAY            ] = "[PLAY]";
	iokey[KEY_FASTFORWARD     ] = "[FASTFORWARD]";
	iokey[KEY_BASSBOOST       ] = "[BASSBOOST]";
	iokey[KEY_PRINT           ] = "[PRINT]";
	iokey[KEY_HP              ] = "[HP]";
	iokey[KEY_CAMERA          ] = "[CAMERA]";
	iokey[KEY_SOUND           ] = "[SOUND]";
	iokey[KEY_QUESTION        ] = "[QUESTION]";
	iokey[KEY_EMAIL           ] = "[EMAIL]";
	iokey[KEY_CHAT            ] = "[CHAT]";
	iokey[KEY_SEARCH          ] = "[SEARCH]";
	iokey[KEY_CONNECT         ] = "[CONNECT]";
	iokey[KEY_FINANCE         ] = "[FINANCE]";
	iokey[KEY_SPORT           ] = "[SPORT]";
	iokey[KEY_SHOP            ] = "[SHOP]";
	iokey[KEY_ALTERASE        ] = "[ALTERASE]";
	iokey[KEY_CANCEL          ] = "[CANCEL]";
	iokey[KEY_BRIGHTNESSDOWN  ] = "[BRIGHTNESSDOWN]";
	iokey[KEY_BRIGHTNESSUP    ] = "[BRIGHTNESSUP]";
	iokey[KEY_MEDIA           ] = "[MEDIA]";
	iokey[KEY_SWITCHVIDEOMODE ] = "[SWITCHVIDEOMODE]";
	iokey[KEY_KBDILLUMTOGGLE  ] = "[KBDILLUMTOGGLE]";
	iokey[KEY_KBDILLUMDOWN    ] = "[KBDILLUMDOWN]";
	iokey[KEY_KBDILLUMUP      ] = "[KBDILLUMUP]";
	iokey[KEY_SEND            ] = "[SEND]";
	iokey[KEY_REPLY           ] = "[REPLY]";
	iokey[KEY_FORWARDMAIL     ] = "[FORWARDMAIL]";
	iokey[KEY_SAVE            ] = "[SAVE]";
	iokey[KEY_DOCUMENTS       ] = "[DOCUMENTS]";
	iokey[KEY_BATTERY         ] = "[BATTERY]";
	iokey[KEY_BLUETOOTH       ] = "[BLUETOOTH]";
	iokey[KEY_WLAN            ] = "[WLAN]";
	iokey[KEY_UWB             ] = "[UWB]";
	iokey[KEY_UNKNOWN         ] = "[UNKNOWN]";
	iokey[KEY_VIDEO_NEXT      ] = "[VIDEO_NEXT]";
	iokey[KEY_VIDEO_PREV      ] = "[VIDEO_PREV]";
	iokey[KEY_BRIGHTNESS_CYCLE] = "[BRIGHTNESS_CYCLE]";
	iokey[KEY_BRIGHTNESS_ZERO ] = "[BRIGHTNESS_ZERO]";
	iokey[KEY_DISPLAY_OFF     ] = "[DISPLAY_OFF]";
	iokey[KEY_WIMAX           ] = "[WIMAX]";
	iokey[KEY_RFKILL          ] = "[RFKILL]";
}

char read_ioctl_value(char **org) {

	init_iokey();
	
	int i;
	char *key;
	char ch;
	int icnt = sizeof(iokey) / sizeof(char *); 

	rinput_log( 
		"Search items in iokey : [%d]\n", (int)icnt);	

	if (**org != '[') {
		rinput_log( 
			"NA in read_ioctl: %x, %s, %c\n", org, *org, **org);
		return 0x00;
	}
		
	rinput_log( 
		"at read_ioctl --- %x, %s, %c\n", org, *org, **org);	
	
	for(i = 0; i < icnt; i++) {

		//rinput_log( 
		//	" ( iokey[%d]==%s )",i, ( iokey[i]==NULL )?"NULL":iokey[i]);

		if ( iokey[i]==NULL ) continue;
		
		key = iokey[i];
		int key_len = strlen(key);
		//rinput_log( 
		//	" Check ctl from value : %x, %s/%s[%d], %c\n", org, *org, key,key_len, **org);

		if ( strncmp(*org, key, key_len)==0 ) {
			ch = (char)i;
			(*org)+= key_len;
			return ch;
		}
	}
	
	rinput_log( 
		"fail to search : %x, %s, %c\n", org, *org, **org);
	return 0x00;
	
}

char *ioenv[27];

void init_ioenv() {
	
	if (ioenv[0     ] != NULL) return;
	memset(ioenv, 0x00, sizeof(ioenv));
	
	ioenv[0     ] = "<LEFTCTRL_DOWN>";
	ioenv[1     ] = "<LEFTCTRL_HOLD>";
	ioenv[2     ] = "<LEFTCTRL_UP>";
	ioenv[3     ] = "<RIGHTCTRL_DOWN>";
	ioenv[4     ] = "<RIGHTCTRL_HOLD>";
	ioenv[5     ] = "<RIGHTCTRL_UP>";
	ioenv[6     ] = "<LEFTALT_DOWN>";
	ioenv[7     ] = "<LEFTALT_HOLD>";
	ioenv[8     ] = "<LEFTALT_UP>";
	ioenv[9     ] = "<RIGHTALT_DOWN>";
	ioenv[10    ] = "<RIGHTALT_HOLD>";
	ioenv[11    ] = "<RIGHTALT_UP>";
	ioenv[12    ] = "<LEFTSHIFT_DOWN>";
	ioenv[13    ] = "<LEFTSHIFT_HOLD>";
	ioenv[14    ] = "<LEFTSHIFT_UP>";
	ioenv[15    ] = "<RIGHTSHIFT_DOWN>";
	ioenv[16    ] = "<RIGHTSHIFT_HOLD>";
    ioenv[17    ] = "<RIGHTSHIFT_UP>";
	ioenv[18    ] = "<LEFTMETA_DOWN>";
	ioenv[19    ] = "<LEFTMETA_HOLD>";
	ioenv[20    ] = "<LEFTMETA_UP>";
	ioenv[21    ] = "<RIGHTMETA_DOWN>";
	ioenv[22    ] = "<RIGHTMETA_HOLD>";
    ioenv[23    ] = "<RIGHTMETA_UP>";
	ioenv[24    ] = "<COMPOSE_DOWN>";
	ioenv[25    ] = "<COMPOSE_HOLD>";
    ioenv[26    ] = "<COMPOSE_UP>";
    ioenv[27    ] = "<COMPOSE_UP>";
	return;
}

char get_ioctl_key(char *k) {
	char s[32];
	char *ss;
	int i=0;

	i = 0;
	memset(s, 0x00, sizeof(s));
	if (*k == '<') {
		s[i]='[';
		for (i=1;*(k+i)!='_';i++) {s[i]=*(k+i);}
		s[i]=']';
	} else if (*k == '[') {
		s[i]='[';
		for (i=1;*(k+i)!=']';i++) {	s[i]=*(k+i);}
		s[i]=']';
	}
	ss = s;
	rinput_log( 
		"define search key : %s, %s, %c", ss, s, k);
	
	//return key value from uinput.sh
	return read_ioctl_value(&ss);
}

char get_ioctl_action(char *k) {

	if ( strstr(k, "_DOWN")>0 ) return 1;
	if ( strstr(k, "_HOLD")>0 ) return 2;
	if ( strstr(k, "_UP")>0 ) return 0;
	return 0;
}

char set_env_value(int fd, char **org) {

	init_ioenv();
	
	int i;
	char *key;
	char ch;
	int  code = 0;
	int  value = 0;
	int  action;
	int icnt = sizeof(ioenv)/sizeof(*ioenv);
	
	rinput_log( 
			"at set env : --- %x, %s, %c", org, *org, **org);	
	if (**org != '<') return 0x00;
	
	for(i=0; i<icnt; i++) {

		if ( ioenv[i]==NULL ) continue;
		
		key = ioenv[i];
		int key_len = strlen(key);

		if ( strncmp(*org, key, key_len)==0 ) {

			code = get_ioctl_key(key);
			if (code != KEY_UNKNOWN) {
				value = get_ioctl_action(key);
				
				ioevent(fd, EV_KEY, code, value);
				ioevent(fd, EV_SYN, SYN_REPORT, 0);
			} else {
				rinput_log( 
						"fail to find env : --- %x, %s, %c\n", org, *org, **org);	
			}
			
			(*org)+= key_len;
			return code;
		}
	}
	
	rinput_log( 
			"fail to find env : --- %x, %s, %c\n", org, *org, **org);	
	return 0x00;
}

char *last_exec_buf = NULL;
char set_ctl(int fd, char **org) {

	int  value = 0;
	char str[256];
	char ch = 0x00;

	rinput_log( 
			"at ctl env : --- %x, %s, %c\n", org, *org, **org);	
	if (**org != '{') {
		printf("first character is not curl brass.\n");
		return 0x00;
	}

    char *end = strstr(*org, "}");
	int key_len = end - *org + 1;

	if ( strncmp(*org, "{USLEEP ", strlen("{USLEEP "))==0 ) {

		char *sss = *org + strlen("{USLEEP ");
		int i = 0;
		memset(str, 0x00, sizeof(str));
	    while(*sss != '}') {
			if (*sss == ' ') {sss++; continue;}
			str[i++] = *sss++;
		}
		rinput_log( "USLEEP time: [%s]\n", str);
		value = atoi(str);
		usleep(value);
	
		ch = 0x01;
	} else if ( strncmp(*org, "{OPEN_TERMINAL}", strlen("{OPEN_TERMINAL}"))==0 ) {
	
		mousemove(fd, EV_REL, 1, 1);
		usleep(2000);
		toggled(fd, EV_KEY, BTN_RIGHT, 1);
		usleep(2000);
		toggled(fd, EV_KEY, KEY_T, 1);
		rinput_log( 
			"open new lxterminal to use rinput\n");
			
		ch = 0x02;
	} else if ( strncmp(*org, "{EXEC ", strlen("{EXEC "))==0 ) {
		char *sss = *org + strlen("{EXEC ");
		
		int i = 0;
		memset(str, 0x00, sizeof(str));
	    while(*sss != '}') {
			str[i++] = *sss++;
		}
		rinput_log( "EXEC [%s]\n", str);
		
		if (last_exec_buf!=NULL) free(last_exec_buf);
		last_exec_buf = exec(str);
		//rinput_log( "EXEC [%s]\n", str);
		rinput_log( "  --  [%s]\n", last_exec_buf); 
		
	
		ch = 0x03;
	}
	
	(*org)+= key_len;
	rinput_log( "set_ctl:return value is [%d];", ch);

	return ch;
}


int	setdevice(int fd, char *name) {
    int i =  0;
	int result = 0;
	
	//use keyboard
    result = ioctl(fd, UI_SET_EVBIT, EV_KEY);
    printf("ioctl(fd, UI_SET_EVBIT, EV_KEY):%d..\n", result);
    if(result < 0){ die("error: ioctl"); }
 
	//use mouse left button
    result = ioctl(fd, UI_SET_KEYBIT, BTN_LEFT);
    printf("ioctl(fd, UI_SET_KEYBIT, BTN_LEFT):%d..\n", result);    
    if(result < 0) { die("error: ioctl"); }

	//use mouse left button
    result = ioctl(fd, UI_SET_KEYBIT, BTN_RIGHT);
    printf("ioctl(fd, UI_SET_KEYBIT, BTN_RIGHT):%d..\n", result);    
    if(result < 0) { die("error: ioctl"); }
	
	//use mouse
    result = ioctl(fd, UI_SET_EVBIT, EV_REL);
    printf("ioctl(fd, UI_SET_EVBIT, EV_REL):%d..\n", result);
    if(result < 0){ die("error: ioctl");}

	//use mouse y axis
    result = ioctl(fd, UI_SET_RELBIT, REL_X);
    printf("ioctl(fd, UI_SET_RELBIT, REL_X):%d..\n", result);
    if(result < 0){ die("error: ioctl");}

	//use mouse y axis
    result = ioctl(fd, UI_SET_RELBIT, REL_Y);
    printf("ioctl(fd, UI_SET_RELBIT, REL_Y):%d..\n", result);
    if(result < 0) { die("error: ioctl");}

	//use keyboard keys
    for (i=0; i < 255; i++){
        ioctl(fd, UI_SET_KEYBIT, i);
    }
	
    struct uinput_user_dev uidev;
    memset(&uidev, 0, sizeof(uidev));
	
    snprintf(uidev.name, UINPUT_MAX_NAME_SIZE, "%s", name);
    uidev.id.bustype = BUS_USB;
    uidev.id.vendor  = 0x1;
    uidev.id.product = 0x1;
    uidev.id.version = 1;
	
    if(write(fd, &uidev, sizeof(uidev)) < 0) die("error: write");
	
    if(ioctl(fd, UI_DEV_CREATE) < 0) 
		die("error: ioctl(fd, UI_DEV_CREATE)");
	
	return 0;
}

int setlistener(int port) {	
	int listenfd = 0;
    listenfd = socket(AF_INET, SOCK_STREAM, 0);
    struct sockaddr_in serv_addr; 
    memset(&serv_addr, '0', sizeof(serv_addr));

    serv_addr.sin_family = AF_INET;
    serv_addr.sin_addr.s_addr = htonl(INADDR_ANY);
    serv_addr.sin_port = htons(port); 

    bind(listenfd, (struct sockaddr*)&serv_addr, sizeof(serv_addr)); 
	
	return listenfd;
}


int remote_ctl(void)
{
	//for devide
    int                    fd;

	//for listener
    int listenport=0, listenfd = 0, connfd = 0;

    char sendBuff[1025];
    memset(sendBuff, 0x00, sizeof(sendBuff)); 
	int readlen = 0;
    char receiveBuff[1025];
    memset(receiveBuff, 0x00, sizeof(receiveBuff)); 
    time_t ticks = 0, lastticks = 0; 
	
    fd = open("/dev/uinput", O_WRONLY | O_NONBLOCK);
    if(fd < 0) die("error: open");
	
	//set event device
	setdevice(fd, "uinput-hive");

	//set socket to listen with port
	listenport = atoi(file_get_contents("/etc/hive/.config/rinput.port"));
	listenfd = setlistener(listenport);
	//listenfd = setlistener(5000);
    listen(listenfd, 10); 

    while(1) {

		//receive message from remote
        connfd = accept(listenfd, (struct sockaddr*)NULL, NULL); 
		
		memset(receiveBuff, 0x00, sizeof(receiveBuff)); 
		readlen = read( connfd,receiveBuff, sizeof(receiveBuff) );

		char *recv = receiveBuff;
		char ch;
		char *ref;
		char *pro;
		while( recv[0]!=0x00 ) {
			ref =  recv;
			rinput_log( "string [%s] start with [%c] ...", recv, recv[0]); 
			if (recv[0] == '<') {
				pro = "<>:SET ENV";
				ch = set_env_value(fd, &recv);
				if (ch == 0x00 )  break;
				//ioctl_env(fd, EV_KEY, ch, 1);
			} else if (recv[0] == '[') {
				pro = "[]:SET KEY";
				ch = read_ioctl_value(&recv);
				if (ch) ioctl_toggled(fd, EV_KEY, ch, 1);
				if (ch == 0x00 ) break;
			} else if (recv[0] == '{') {
				pro = "{}:SET CTL";
				ch = set_ctl(fd, &recv);
				if (ch == 0x00 ) break;
			} else {
				pro = "ch:SET CHAR";
				//rinput_log( " @for char\n"); 
				ch = recv[0];
				ascii_toggled(fd, EV_KEY, ch, 1);
				recv++;
			}
			rinput_log( 
				"output to %s will be key[%d] for %s", pro, ch, ref);
		} 

        ticks = time(NULL);
		if (ch == 0x03) {
			rinput_log( last_exec_buf);
			write(connfd, last_exec_buf, strlen(last_exec_buf)); 
			free(last_exec_buf); last_exec_buf = NULL;
		} else {
			snprintf(sendBuff, sizeof(sendBuff), 
				"execute[%s] at %.24s .. remina[%s]\r\n",receiveBuff, ctime(&ticks), recv);
			write(connfd, sendBuff, strlen(sendBuff)); 
		}
        close(connfd);
		
    }

    sleep(2);
    if(ioctl(fd, UI_DEV_DESTROY) < 0) die("error: ioctl");
    close(fd);

    return 0;
}

//activate every 10 minutes
int keep_wakeup_display()
{
    int                    fd;

	fd = open("/dev/uinput", O_WRONLY | O_NONBLOCK);
	if(fd < 0) die("error: open");
	setdevice(fd, "uinput-activate");
		
    while(1) {
		
		//set event device
		mousemove(fd, EV_REL, 1, 1);
		mousemove(fd, EV_REL, -1, -1);

		sleep(120);
    }

	if(ioctl(fd, UI_DEV_DESTROY) < 0) die("error: ioctl");
	close(fd);
	
    return 0;
}

int daemonize(int (*daemon_func)())
{
    int pid;
	char buf[254];
	if (getcwd(buf, sizeof(buf)) != NULL) {
	    fprintf(stdout, "Working dir: %s\n", buf);
	} else {
	   perror("getcwd() error");
	}

    // fork 
    pid = fork();
    printf("pid = [%d] \n", pid);
 
    // log for fork error
    if(pid < 0){
        printf("fork Error... : return is [%d] \n", pid );
        perror("fork error : ");
        exit(0);
    // log for  parrent process kill
    }else if (pid > 0){
        printf("daemon process : [%d] - ignition process : [%d] \n", pid, getpid());
		sprintf(buf, "%d", pid);
		file_put_contents("/run/rinput.pid", buf);
        exit(0);
    // normal log
    }else if(pid == 0){
        printf("process : [%d]\n", getpid());
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
 
    int cnt = 0;
     // insert the code to repeat
	int sleep_time = 2;
    while(1) {
        //resurrection for daemon killed
		fprintf(stdout, "Current time: %ld\n", (long)time(NULL));
		
		if((pid = fork()) < 0) {
			printf("fork error : restart daemon");
		}else if(pid == 0) {
			daemon_func();
			//exit(0);
		}/*else if(pid > 0) {
			int ret; 
			wait(&ret); 
		}*/

		if((pid = fork()) < 0) {
			printf("fork error : restart daemon");
		}else if(pid == 0) {
			keep_wakeup_display();
			exit(0);
		}else if(pid > 0) {
			int ret; 
			wait(&ret); 
		}
		
		rinput_log( "sleep [%d]sec repeated [%d]", sleep_time, cnt); 
        sleep(sleep_time);
		cnt++;
    }
}

void main(int argc, char *argv[])
{
	daemonize(&remote_ctl);
}
