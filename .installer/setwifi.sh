#!/bin/bash
# create by AnHive. Co., Ltd. 2017/04
# add new ap 

CONF=/etc/wpa_supplicant/wpa_supplicant.conf;

#check the current status of wireless connection
IFC=$(ifconfig wlan0 | grep "inet addr" | grep -oE '\b([0-9]{1,3}\.){3}[0-9]{1,3}\b' | head -1)
echo "infterface .. $IFC"
if [ "x$IFC" != "x" ]; then
        sudo cat $CONF

        echo "wireless is connected to a AP[$IFC]"
        read -p "Add new WiFi AP Y/n: " NEWAP
        if [ "x$NEWAP" == "xn" ] || [ "x$NEWAP" == "xN" ] ; then
            quit to add new WiFi AP by press $NEWAP.
            exit 0
        fi
fi

#backup orignanl file
sudo cp -p $CONF /run/shm/wpa_supplicant.conf.org
      
#select available hot spop
function getssid () {
    sudo iwlist scan  2>&1 | sed 's/ //g' | grep ESSID 
    SSIDS=";$(sudo iwlist scan  2>&1 | grep ESSID |                 sed 's/ESSID:"\(.*\)"/\1/' | sed 's/ //g' | tr '\n' ';')"
    SSID=""
    while [ "x$SSID" == "x" ]
    do
     read -p "input SSID to connect WiFi AP: " SSID
     echo "input [$SSID]"
     if echo "$SSIDS" | grep -q ";$SSID;"; then
        echo "$SIDS exist in list."
     else
        echo "no matched"
        SSID=""
     fi
    done
}

#input access key and modify wpa configuration
function setwpa () {
    PSK=""
    while [ "x$PSK" == "x" ]
    do
        read -p "input Access Code (PSK) for $SSID : " PSK
        echo "input [$PSK]"
        if [ "x$PSK" != "x" ]; then

            SS="\nnetwork={\n     ssid=\\\"$SSID\\\"\n     psk=\\\"$PSK\\\"\n}\n"
            sudo sh -c "echo \"$SS\" >> \/etc\/wpa_supplicant\/wpa_supplicant.conf"
            sudo cat /etc/wpa_supplicant/wpa_supplicant.conf

            return
        fi
    done
}

#try interface connection
function checkconnection () {
   COUNT=30
   while (( $COUNT > 0 ))
   do
        echo "Waiting wifi connection. reimain[ $COUNT ] seconds"
        sleep 1
        IFC=$(ifconfig wlan0 | grep "inet addr" | grep -oE '\b([0-9]{1,3}\.){3}[0-9]{1,3}\b' | head -1 )
        if [ "x$IFC" != "x" ]; then
            echo "wifi IP address .. $IFC"
            sudo rm /run/shm/wpa_supplicant.conf.org
            exit 0
        fi
        : $((COUNT--))
    done
    sudo cp -p /run/shm/wpa_supplicant.conf.org $CONF 
    echo "Fail to commect [$SSID] with PSK password, Try again"
    return -1
}

#try to get success to connection
RC=-1
while [ $RC != 0 ]
do 
    getssid
    setwpa
    sudo ifdown wlan0 && sudo ifup wlan0
    checkconnection
    RC=$?
done

