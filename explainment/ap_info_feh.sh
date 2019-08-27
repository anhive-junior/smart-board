#!/bin/bash
# ap enable photo
# HOW TO CONNECT TO SMARTB
rm -f explain.png.txt 
ETH=$(ifconfig eth0 | grep inet | head -1 | awk '{print $2}')
WLA=$(ifconfig wlan0 | grep inet | head -1 | awk '{print $2}')
SSID=$(cat /home/pi/ap/hostapd_sign.conf | grep ssid | head -1 | cut -d"=" -f2)
PASS=$(cat /home/pi/ap/hostapd_sign.conf | grep pass | head -1 | cut -d"=" -f2)
echo "유선 IP : "$ETH
echo "무선 IP : "$WLA
echo "와이파이 이름 : "$SSID 
if [ $PASS == "surprisebox" ]; then
    echo "비밀번호 : "$PASS
fi
echo ""
echo ""
