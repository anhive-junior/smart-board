#!/bin/bash
#####
# get install source list at wiki

whiptail --title "Install SmartBoard" --yesno "            Do you want Install SmartBoard?" 8 60

# setting location
wiki_location="https://raw.githubusercontent.com/wiki/anhive-junior/smart-board/Install-Smart-Board.md"
download_file="./data_smartboard"
count=0
scripts_name=(
    ""
    "step.01 to set raspbarry pi configuration (raspi-config) - 1/7"
    "step.02 to set RASPBIAN mirror (KAIST) - 2/7"
    "step.03 install web server(apache) - 3/7"
    "step.04 to install multimedia application(FEH) - 4/7"
    "step.05 to install Internet Photo frame(SurpriseBox) - 5/7"
    "step.06 to insall AP applications(HOSTAP) - 6/7"
    "step.07 to install additional program(evtest imagemagick miniupnpc) - 7/7"
)

wget $wiki_location -O $download_file
IFS=$'\n'
for i in $(cat $download_file)
do
    if [ "$i" == "\`\`\`" ]; then
        count=$((count+1))
        continue
    fi
    if (( $count % 2 == 1 )); then
        echo "$i" >> install.sh
    fi
done

#####
# install and configuration
check="##### be smart"
count=0
skip=false
IFS=$'\n'
for i in $(cat $data_smartboard)
do
    if [ "$i" = "$check" ]; then
        count=$((count+1))
        whiptail --title "Install SmartBoard" --yesno "${scripts_name[$count]}" 7 $((${#scripts_name[$count]} + 5))
        if (( $? == 0 )); then
            skip=false
        else
            skip=true
        fi
    if [ "$i" = "$check wifi" ]; then
        break
    fi
    if (( $skip == true )); then
        continue
    else
        $i ## process line by line
    fi
done

#####
# raspbarry pi ap activity configuration
if ( whiptail --title "Install SmartBoard" --yesno "you want to activate the Raspberry WiFi Router?" 7 60 ); then
    cd ~/ap
    sudo ./set_ap.sh enable
fi

rm data_smartboard
rm install.sh