#!/bin/bash
# get install source list at wiki

#setting location
wiki_location="https://raw.githubusercontent.com/wiki/anhive-junior/smart-board/Install-Smart-Board.md"
download_file="./data_smartboard"
count=0


wget $wiki_location -O $download_file


IFS=$'\n'
for i in $(cat $download_file)
do
	if [ "$i" == "\`\`\`" ]; then
		count=$((count+1))
		continue
	fi
	if (( $count % 2 == 1 )); then

	fi
done