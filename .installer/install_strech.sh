#!/bin/bash
# create by AnHive. Co., LTd.
 
function confirm_to_continue() {
    read -p "$1 ------continue Y/n" CONTINUE
    if [ "x$CONTINUE" == "xn" ] || [ "x$CONTINUE" == "xN" ]
    then
        return -1;
    fi
    return 0
}
 
function echo_log() {
     echo $1 | tee -a install.log
}
 
confirm_to_continue "--------step.1 to set personalization"
rc=$?; if [[ $rc == 0 ]]; then
sudo raspi-config
 
fi
#####################################
confirm_to_continue "--------step.2 to set WiFi connections"
rc=$?; if [[ $rc == 0 ]]; then
 
mkdir -p ~pi/bin
cat << EOF > ~pi/bin/setwifi.sh
#!/bin/bash
# create by AnHive. Co., Ltd. 2017/04
# add new ap 
 
CONF=/etc/wpa_supplicant/wpa_supplicant.conf;
 
#check the current status of wireless connection
IFC=\$(ifconfig wlan0 | grep "inet" | grep -oE '\b([0-9]{1,3}\.){3}[0-9]{1,3}\b' | head -1)
echo "infterface .. \$IFC"
if [ "x\$IFC" != "x" ]; then
        sudo cat \$CONF
 
        echo "wireless is connected to a AP[\$IFC]"
        read -p "Add new WiFi AP Y/n: " NEWAP
        if [ "x\$NEWAP" == "xn" ] || [ "x\$NEWAP" == "xN" ] ; then
            quit to add new WiFi AP by press \$NEWAP.
            exit 0
        fi
fi
 
#backup orignanl file
sudo cp -p \$CONF /run/shm/wpa_supplicant.conf.org
 
#select available hot spop
function getssid () {
    sudo iwlist scan  2>&1 | sed 's/ //g' | grep ESSID 
    SSIDS=";\$(sudo iwlist scan  2>&1 | grep ESSID | \
                sed 's/ESSID:"\(.*\)"/\1/' | sed 's/ //g' | tr '\n' ';')"
    SSID=""
    while [ "x\$SSID" == "x" ]
    do
     read -p "input SSID to connect WiFi AP: " SSID
     echo "input [\$SSID]"
     if echo "\$SSIDS" | grep -q ";\$SSID;"; then
        echo "\$SIDS exist in list."
     else
        echo "no matched"
        SSID=""
     fi
    done
}
 
#input access key and modify wpa configuration
function setwpa () {
    PSK=""
    while [ "x\$PSK" == "x" ]
    do
        read -p "input Access Code (PSK) for \$SSID : " PSK
        echo "input [\$PSK]"
        if [ "x\$PSK" != "x" ]; then
 
            SS="\nnetwork={\n     ssid=\\\\\"\$SSID\\\\\"\n     psk=\\\\\"\$PSK\\\\\"\n}\n"
            sudo sh -c "echo \"\$SS\" >> \/etc\/wpa_supplicant\/wpa_supplicant.conf"
            sudo cat /etc/wpa_supplicant/wpa_supplicant.conf
 
            return
        fi
    done
}
 
#try interface connection
function checkconnection () {
   COUNT=30
   while (( \$COUNT > 0 ))
   do
        echo "Waiting wifi connection. reimain[ \$COUNT ] seconds"
        sleep 1
        IFC=\$(ifconfig wlan0 | grep "inet" | grep -oE '\b([0-9]{1,3}\.){3}[0-9]{1,3}\b' | head -1 )
        if [ "x\$IFC" != "x" ]; then
            echo "wifi IP address .. \$IFC"
            sudo rm /run/shm/wpa_supplicant.conf.org
            exit 0
        fi
        : \$((COUNT--))
    done
    sudo cp -p /run/shm/wpa_supplicant.conf.org \$CONF 
    echo "Fail to commect [\$SSID] with PSK password, Try again"
    return -1
}
 
#try to get success to connection
RC=-1
while [ \$RC != 0 ]
do 
    getssid
    setwpa
    sudo service dhcpcd restart
    checkconnection
    RC=\$?
done
 
EOF
chmod 755 ~pi/bin/setwifi.sh
~pi/bin/setwifi.sh
 
# restart interface
ifconfig wlan0
#end of script
 
fi
#####################################
confirm_to_continue "--------step.3 to set RASPBIAN mirror"
rc=$?; if [[ $rc == 0 ]]; then
 
echo_log "  change archive source from origine to KAIST mirror"
#change source list
ls -la /etc/apt/sources.list
 
module=$(head -n 1 /etc/apt/sources.list | awk '{print $3}')
echo "deb http://ftp.kaist.ac.kr/raspbian/raspbian/ $module main contrib non-free rpi" > /run/shm/s.d
cat /run/shm/s.d
if [ -e /etc/apt/sources.list.org ]
then
	sudo cp /etc/apt/sources.list.org /etc/apt/sources.list 
else
  	sudo cp /etc/apt/sources.list /etc/apt/sources.list.org
fi
 
sudo sh -c "sed -i 's/deb http/#deb http/g' /etc/apt/sources.list"
sudo sh -c "cat /run/shm/s.d /etc/apt/sources.list > /etc/apt/sources.list.net"
cat /etc/apt/sources.list.net
sudo sh -c "cat /run/shm/s.d /etc/apt/sources.list > /etc/apt/sources.list.net"
sudo mv /etc/apt/sources.list.net /etc/apt/sources.list
 
ls -la /etc/apt/sources.list
cat /etc/apt/sources.list
# end of script
 
sudo apt-get update
# end of script
 
# sudo apt-get -y upgrade
sudo apt-get clean
# end of script
else
	sudo apt-get update
fi
#####################################
confirm_to_continue "--------step.4 to install file share(SAMBA)"
rc=$?; if [[ $rc == 0 ]]; then
echo "Install samba package to share document over windows file sharing protocol"
sudo apt-get -y install samba samba-common-bin
# end of script
 
sudo smbpasswd -a pi
# end of script
 
cat << EOF > smb.tmp
[pi]
path = /home/pi
comment = PI SAMBA SERVER
writable = no
browseable = yes
create mask = 0777
#public = yes
read list = pi
write list = pi
EOF
sudo sh -c "cat smb.tmp >> \/etc\/samba\/smb.conf"
 
tail /etc/samba/smb.conf
# end of script
 
fi
#####################################
#web server installation  
confirm_to_continue "------- step.5 to install web server(APACHE)"
rc=$?; if [[ $rc == 0 ]]; then
 
sudo apt-get -y install apache2
# end of script
 
#set root for web
sudo sed -i 's/DocumentRoot \/var\/www\/html/DocumentRoot \/var\/www/' /etc/apache2/sites-enabled/000-default.conf
grep 'DocumentRoot \/var\/www' /etc/apache2/sites-enabled/000-default.conf
 
# share contents between www-data and pi
sudo usermod -a -G www-data pi && id pi
sudo usermod -a -G pi www-data && id www-data
# end of script
 
wget localhost -O - | head -10
# end of script
 
# set hive package space
sudo mkdir -p /etc/hive
# end of script
 
 
#####################################
#web application server installation  
sudo apt-get -y install php libapache2-mod-php php-mcrypt php-gd
# end of script
 
#set for file transfer bigger then 2M
sudo sed -i 's/post_max_size = .M/post_max_size = 2G/' /etc/php/7.0/apache2/php.ini
grep "post_max_size =" /etc/php/7.0/apache2/php.ini
sudo sed -i 's/upload_max_filesize = .M/upload_max_filesize = 2G/' /etc/php/7.0/apache2/php.ini
grep "upload_max_filesize =" /etc/php/7.0/apache2/php.ini
# end of script
 
sudo service apache2 restart
# end of script
 
sudo sh -c "echo '<?= phpinfo();?>' > /var/www/test.php"
# end of script
 
wget localhost/test.php -O - | head -10
# end of script
 
fi
#####################################
confirm_to_continue "------- step.6 to install multimedia applicatiopn(FEH)"
rc=$?; if [[ $rc == 0 ]]; then
 
#slide show, video player
sudo apt-get -y install feh omxplayer
# end of script
 
# short key registration
cd ~
SS="
<keybind key=\"A-C-f\">
<action name=\"Execute\"><command>/etc/hive/signage/feh.sh</command></action>
</keybind>
<keybind key=\"A-C-o\">
<action name=\"Execute\"><command>/etc/hive/signage/omx.sh</command></action>
</keybind>
 
</keyboard>
"
echo "$SS" > src.txt
 
# for lxde as default
#if [ -e ~/.config/openbox/lxde-pi-rc.xml ]
#then
#	sed -i '/<\/keyboard>/r src.txt' ~/.config/openbox/lxde-pi-rc.xml
#	sed -i '0,/<\/keyboard>/{s/<\/keyboard>//}' ~/.config/openbox/lxde-pi-rc.xml
#	ls -la  ~/.config/openbox/lxde-pi-rc.xml
#	grep signage ~/.config/openbox/lxde-pi-rc.xml
#else
#	echo_log "fail to change [~/.config/openbox/lxde-pi-rc.xml]"
#fi
 
 
if [ -d /home/pi/.config/openbox ]
then
  sudo rm -rf /home/pi/.config/openbox /home/pi/.config/lxsession
fi
 
# backup openbox of xml file
if [ ! -f /etc/xdg/openbox/lxde-pi-rc.xml.bak ]
then
  sudo cp /etc/xdg/openbox/lxde-pi-rc.xml /etc/xdg/openbox/lxde-pi-rc.xml.bak
fi
 
# for lxde as default
if [ -e /etc/xdg/openbox/lxde-pi-rc.xml ]
then
	sudo sed -i '/<\/keyboard>/r src.txt' /etc/xdg/openbox/lxde-pi-rc.xml
	sudo sed -i '0,/<\/keyboard>/{s/<\/keyboard>//}' /etc/xdg/openbox/lxde-pi-rc.xml
	ls -la  /etc/xdg/openbox/lxde-pi-rc.xml
	grep signage /etc/xdg/openbox/lxde-pi-rc.xml
else
	echo "fail to change [/etc/xdg/openbox/lxde-pi-rc.xml]"
fi
 
 
rm src.txt
# end of script
 
#
echo_log extent to full screen when monitor is connected
sudo sed -i.old 's/#disable_overscan=1/disable_overscan=1/g' /boot/config.txt
grep disable_overscan /boot/config.txt
# end of script
 
 
fi
#####################################
confirm_to_continue "------- step.7 to install Internet Photoframe(SurpriseBox)"
rc=$?; if [[ $rc == 0 ]]; then
 
#remote key input
cd ~
cp ~/smart-board/.installer/core/rinput ./ && cd ~/rinput
make
sudo make install > /dev/null &
# end of script
# schedule job
cd ~
cp ~/smart-board/.installer/core/scheduler ./ && cd ~/scheduler
cp Makefile_pi Makefile
make
sudo make install > /dev/null &
 
sudo sh -c "echo -n > /etc/hive/tasks/schedule.tasks"
sudo chmod 666 /etc/hive/tasks/schedule.tasks
sudo chmod 777 /etc/hive/tasks
sudo service timeworks start
# end of script
 
# download signage
cd ~
mkdir -p ~/signage && cd ~/signage
wget thebolle.com/archive/signage.tar.gz -O - | tar -xvzpf -
# end of script
 
# set default landing page
cd ~/signage/.installer
sudo ln -sfT ~/signage /var/www/signage
sudo cp res/index.html /var/www/index.html
 
# set permission
chmod -R g+w ~/signage/custom/sample
chmod -R g+w ~/signage/validator
 
# set folders
mkdir -p ~/media/slide
mkdir -p ~/media/playlist
mkdir -p ~/media/captions
ln -sfT ~/media/captions ~/media/playlist/captions
mkdir -p ~/media/thumbs
mkdir -p ~/media/video
mkdir -p ~/media/info
 
# open to web
sudo ln -sfT ~/media /var/www/media
chmod -R 775 ~/media
# end of script
 
sudo mkdir -p /etc/hive/signage
sudo ln res/feh.sh /etc/hive/signage/feh.sh
sudo ln res/omx.sh /etc/hive/signage/omx.sh
sudo chmod -R 775 /etc/hive/signage
ls -la /etc/hive/signage/
# end of script
 
sudo mkdir -p /etc/hive/default
sudo ln -sfT ~/signage /etc/hive/default/signage
ls -al /etc/hive/default/signage
# end of script
 
fi
 
# for autostart in pixel
if [ -f /etc/xdg/lxsession/LXDE-pi/autostart.bak ]; then
   echo "this file already fix"
else
   sudo cp /etc/xdg/lxsession/LXDE-pi/autostart /etc/xdg/lxsession/LXDE-pi/autostart.bak
   sudo sh -c "echo '/etc/hive/signage/feh.sh' >> /etc/xdg/lxsession/LXDE-pi/autostart"
fi
# end of script
#####################################
confirm_to_continue "------- step.8 to install suppliment package(Utilities)"
rc=$?; if [[ $rc == 0 ]]; then
 
# additional network packages
sudo apt-get -y install conntrack
# end of script
 
sudo mkdir -p /etc/hive/bin
sudo cp res/cutconnect /etc/hive/bin/cutconnect
sudo cp res/rmtrack /etc/hive/bin/rmtrack
sudo cp res/shellcmd /etc/hive/bin/shellcmd
sudo chmod -R 755 /etc/hive/bin
# end of script
 
sudo sh -c "echo 'www-data ALL = NOPASSWD: /etc/hive/bin/rmtrack' >> /etc/sudoers.d/sign_sudoers"
sudo sh -c "echo 'www-data ALL = NOPASSWD: /etc/hive/bin/cutconnet' >> /etc/sudoers.d/sign_sudoers"
sudo sh -c "echo 'www-data ALL = NOPASSWD: /sbin/iptables' >> /etc/sudoers.d/sign_sudoers"
sudo sh -c "echo 'www-data ALL = NOPASSWD: /usr/sbin/arp' >> /etc/sudoers.d/sign_sudoers"
 
cat /etc/sudoers.d/sign_sudoers
# end of script
 
cd ~
sudo apt-get -y install evtest imagemagick miniupnpc
# end of script
 
fi
#####################################
confirm_to_continue "------- step.9 to insall AP applications(HOSTAP)"
rc=$?; if [[ $rc == 0 ]]; then
 
sudo apt-get -y install hostapd
# end of script
 
# set path
cd ~ && mkdir -p ap && chmod 775 ap && cd ap
 
#hostapd configuration
SS="#base setting
interface=wlan0
driver=nl80211
hw_mode=g
ieee80211n=1
wmm_enabled=1
channel=5
ssid=AnHive-SurpriseBox
ignore_broadcast_ssid=0
 
#security
wpa=2
wpa_passphrase=surprisebox
wpa_key_mgmt=WPA-PSK
wpa_pairwise=TKIP
rsn_pairwise=CCMP
auth_algs=3
macaddr_acl=0
"
 
echo "$SS" > ~/ap/hostapd_sign.conf
sed -i "s/ //g" ~/ap/hostapd_sign.conf
# set said with hostname 
sed -i "s/ssid=AnHive/ssid=$(hostname)/g" ~/ap/hostapd_sign.conf
cat ~/ap/hostapd_sign.conf
chmod 664 ~/ap/hostapd_sign.conf
 
grep ssid ~/ap/hostapd_sign.conf
# end of script
 
 
sudo mkdir -p /etc/hive/default
sudo chown www-data:www-data /etc/hive/default
sudo chmod 775 /etc/hive/default
sudo ln -sfT ~/ap/hostapd_sign.conf /etc/hive/default/hostapd.conf
# end of script
 
sudo apt-get -y install isc-dhcp-server
# end of script
 
[ ! -e /etc/dhcp/dhcpd.conf.org ] && sudo mv /etc/dhcp/dhcpd.conf /etc/dhcp/dhcpd.conf.org
 
SS="
ddns-update-style none;
ignore client-updates;
authoritative;
option local-wpad code 252 = text;
 
subnet 192.168.201.0 netmask 255.255.255.0 {
        # --- default gateway
        option routers 192.168.201.1;
        # --- Netmask
        option subnet-mask 255.255.255.0;
        # --- Broadcast Address
        option broadcast-address 192.168.201.255;
        # --- Domain name servers, tells the clients which DNS servers to use.
        option domain-name-servers 8.8.8.8, 8.8.4.4;
        option time-offset 0;
        range 192.168.201.11 192.168.201.254;
        default-lease-time 3600;
        max-lease-time 7200;
}
"
 
sudo sh -c "echo \"$SS\" > \/etc\/dhcp\/dhcpd.conf"
cat /etc/dhcp/dhcpd.conf
# end of script
 
#ap startup script
 
SS="#\!/bin/bash
#Initial wifi interface configuration
 
DIR=\$(dirname \$0)
if [ -e \"\$DIR/disable_ap\" ]
then
   echo \"disabled access point by disable_ap file in \$DIR\"
   exit 0
fi
 
CONF=\$1
APIF=\$2
WAN=\$3
 
ifconfig \$APIF up 192.168.201.1 netmask 255.255.255.0
sleep 2
###########Start DHCP, comment out / add relevant section##########
killall dhcpd
sleep 1
if [ \"\$(ps -e | grep dhcpd)\" == \"\" ]; then
        dhcpd \$APIF &
fi
###########
#Enable NAT
iptables --flush
iptables --table nat --flush
iptables --delete-chain
iptables --table nat --delete-chain
iptables --table nat --append POSTROUTING --out-interface \$WAN -j MASQUERADE
iptables --append FORWARD --in-interface \$APIF -j ACCEPT
 
sysctl -w net.ipv4.ip_forward=1
#start hostapd
/usr/sbin/hostapd -B \$CONF
"
 
echo "$SS" > ~pi/ap/initAP
sed -i 's/\\!\/bin/!\/bin/' ~pi/ap/initAP
chmod 755 ~pi/ap/initAP
ls -l ~pi/ap/initAP
# end of script
 
SS="#\!/bin/bash
### BEGIN INIT INFO
# Provides:          anhive
# Required-Start:    \$local_fs \$syslog
# Required-Stop:     \$local_fs \$syslog
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# X-Interactive:     true
# Short-Description: Start/stop sign-hive application
### END INIT INFO
 
PATH=/sbin:/usr/sbin:/bin:/usr/bin
PIDF=/run/sign-hive.pid
PID=\$(cat \$PIDF)
 
do_start () {
  # Start remote input for application 
  sleep 2
  /home/pi/ap/initAP /home/pi/ap/hostapd_sign.conf wlan0 eth0
  echo \$1 > \$PIDF
}
 
do_status () {
  ps -ef | grep \$PID
  return \$PID
}
 
do_stop () {
  # check current status which is good or bat
  kill -9 \$(ps -ef | grep \$PID | awk '{print \$2}')
  rm \$PIDF
}
 
case \"\$1\" in
 start|\"\")
      do_start
      exit \$?
      ;;
 restart|reload|force-reload)
      do_stop
      do_start
      exit \$?
      ;;
 stop)
      do_stop
      exit \$?
      ;;
 status)
      do_status
      exit \$?
      ;;
 *)
      echo \"Usage: sign-hive [start|stop|status]\" >&2
      exit 3
      ;;
esac
"
# set init script to sign-hive
echo "$SS" > ~pi/ap/sign-hive
sed -i 's/\\!\/bin/!\/bin/' ~pi/ap/sign-hive
sudo mv ~pi/ap/sign-hive /etc/init.d/
sudo chmod +x /etc/init.d/sign-hive
ls -l /etc/init.d/sign-hive
 
# regist init script
sudo update-rc.d sign-hive defaults
# end of script
 
 
#####################################
rc=$?; if [[ $rc == 0 ]]; then
 
 
echo "remove this file, access point will be available" > ~pi/ap/disable_ap
# end of script'
 
SS="
# stop to assign ip to wlan0
# uncomment if AP mode 
#denyinterfaces wlan0 
"
sudo sh -c "echo \"$SS\" >> /etc/dhcpcd.conf"
# end of script
 
 
SS="#\!/bin/bash
# AnHive, 2017.01
 
cd ~pi/ap
 
[ \"\$#\" -lt \"1\" ] && echo \"usage \$0 enable\|disagle\" && exit 0
 
if [ \"\$1\" == \"enable\" ]
then
    sudo sed -i 's/#denyinterfaces/denyinterfaces/g' /etc/dhcpcd.conf
    [ -e disable_ap ] && mv disable_ap enable_ap
    sudo sed -i 's/wlan0/wlanX/g' /etc/network/interfaces
elif [ \"\$1\" == \"disable\" ]
then
    sudo sed -i 's/denyinterfaces/#denyinterfaces/g' /etc/dhcpcd.conf
    [ -e enable_ap ] && mv enable_ap disable_ap
    sudo sed -i 's/wlanX/wlan0/g' /etc/network/interfaces
else
  echo \"not defined option \$1.\"
fi
"
 
echo "$SS" > ~pi/ap/set_ap.sh
sed -i 's/\\!\/bin/!\/bin/' ~pi/ap/set_ap.sh
chmod 755 ~pi/ap/set_ap.sh
sudo cp ~pi/ap/set_ap.sh /etc/hive/bin/set_ap.sh
sudo sh -c "echo 'www-data ALL = NOPASSWD: /etc/hive/bin/set_ap.sh' >> /etc/sudoers.d/sign_sudoers"
# end of script
 
fi
#####################################
confirm_to_continue "------- step.10 to change to AP mode and another setting"
 
## remove the piwiz program
sudo apt-get remove --purge piwiz -y
#end of script
 
## remove ssh window
sudo rm -rf /etc/xdg/lxsession/LXDE-pi/sshpwd.sh
#end of script
 
#WI-FI enable
cd ~/ap
sudo ./set_ap.sh enable
sudo reboot
fi
# end of script
