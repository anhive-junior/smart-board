#include <stdio.h> 
#include <stdlib.h> 
#include <unistd.h> 
#include <string.h> 
#include <sys/types.h> 
#include <sys/socket.h> 
#include <arpa/inet.h> 
#include <netinet/in.h> 
#include <sys/ioctl.h>
#include <net/if.h>

#define LPORT 4001 // listen port
#define MAXLINE 1024

char * get_ip(){
    int fd;
    struct ifreq ifr;

    fd = socket(AF_INET, SOCK_DGRAM, 0);

    /* I want to get an IPv4 IP address */
    ifr.ifr_addr.sa_family = AF_INET;

    /* I want IP address attached to "eth0" */
    strncpy(ifr.ifr_name, "wlan0", IFNAMSIZ-1);

    ioctl(fd, SIOCGIFADDR, &ifr);

    close(fd);

    /* display result */

    return inet_ntoa(((struct sockaddr_in *)&ifr.ifr_addr)->sin_addr);
}

int wait_and_send(){
    int sockfd;
    char buffer[MAXLINE];
    char *is_alive = "test";
    char *alive = get_ip();
    struct sockaddr_in servaddr, cliaddr;
    if ( ( sockfd = socket(AF_INET, SOCK_DGRAM, 0)) < 0 ){
        perror("socket creation faild");
        exit(1);
    }

    memset(&servaddr, 0, sizeof(servaddr));
    memset(&cliaddr, 0, sizeof(cliaddr));
    servaddr.sin_family = AF_INET;
    servaddr.sin_addr.s_addr = INADDR_ANY;
    servaddr.sin_port = htons(LPORT);

    if( bind(sockfd, (const struct sockaddr *)&servaddr, sizeof(servaddr)) < 0){
        perror("bind faild");
        exit(1);
    }

    // and then send data
    int len, n;
    n = recvfrom(sockfd, (char *)buffer, MAXLINE, MSG_WAITALL, (struct sockaddr *) &cliaddr, &len);
    buffer[n] = '\0';
    if (  *buffer == *is_alive ){
        // data send
        sendto(sockfd, (const char *)alive, strlen(alive),  
        MSG_CONFIRM, (const struct sockaddr *) &cliaddr, len); 
    }
    close(sockfd);
    return 0;
}

int work(){
    wait_and_send(); // broadcast wait and then send data ;;
}

int daemonnize(){
    pid_t pid;
    if( ( pid = fork() ) < 0) // error fork
        exit(0);
    else if( pid != 0 ) //  PPID exit
        exit(0);
    //chdir("/");
    setsid();

    while(1){
        work();
    }
}

int main(){
    daemonnize();
}