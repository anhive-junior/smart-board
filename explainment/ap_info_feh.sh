#!/bin/bash
# HOW TO CONNECT TO SMARTB
rm -f explain.png.txt 
ETH=$(ifconfig eth0 | grep inet | head -1 | awk '{print $2}')
WLA=$(ifconfig wlan0 | grep inet | head -1 | awk '{print $2}')
SSID=$(cat /home/pi/ap/hostapd_sign.conf | grep ssid | head -1 | cut -d"=" -f2)
PASS=$(cat /home/pi/ap/hostapd_sign.conf | grep pass | head -1 | cut -d"=" -f2)
if [ ${ETH} ]; then
	echo "(유선)접속 주소 : "$ETH
fi
if [ ${WLA} ]; then
	echo "(무선)접속 주소 : "$WLA
fi
if [ -e "/home/pi/ap/enable_ap" ]; then
	echo "와이파이 이름 : "$SSID 
	if [ $PASS == "surprisebox" ]; then
	    echo "비밀번호 : "$PASS
	else 
	    printf "비밀번호 : "
	    for((i=0;i<${#PASS};i++)); do
		printf "%c" x
	    done
	fi
fi
echo ""
echo ""
