#!/bin/bash
#check if source file exists
if [ ! -f $1 ]
then
echo "Specified file does not exist. Exiting..."
exit
fi

dir=$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )
host=""
pass=""
outdir="$dir/temp/"
webdir="/var/www/html/mspec/"
outfile="$(basename $1)"
rm "$outdir$outfile.out" 2> /dev/null
cmd="echo \"\$(hostname),\$(ifconfig \$(route -n | grep '^0.0' | rev | cut -d' ' -f1 | rev) | grep 'inet ' | awk -F':' '{ print \$2 }' | awk -F' ' '{ print \$1 }'),\$(cat /proc/cpuinfo | grep 'model name' | uniq | sed -e 's/model name.*: //' | sed -e 's/^.*CPU//' | tr -d '[[:space:]]' | awk -F'@' '{ print \$1 }'),\$(echo \$((\$(free -g | grep Mem | awk -F':' '{ print \$2 }' | awk -F' ' '{ print \$1 }')+1))GB),\$(echo \$(fdisk -l 2> /dev/null | grep 'Disk /dev/sd' | awk -F',' '{ print \$1 }' | awk -F':' '{ print \$2 }' | sed -e 's/^ //' | sed -e 's/ GB//'))\""

chost()
{
        sshpass -p $PW ssh root@$IP -n -o StrictHostKeyChecking=no "${cmd}" </dev/null
}

while read line
do
PW=$(echo $line | awk -F' ' '{ print $2}')
IP=$(echo $line | awk -F' ' '{ print $1}')
chost >> $outdir$outfile.out
done < $1

if [ -f $outdir$outfile.out ]
then
echo "	Created $outdir$outfile.out"
else
echo "	Failed to create results file: $outdir$outfile.out"
echo "	Please try again"
fi

#Run post-processing on resulting file
$dir/pp-mspec.php $outdir $outfile.out

if [ -f $webdir$outfile.html ]
then
echo -e "	Complete! View the details at http://$(hostname)/mspec/$outfile.html"
else
echo "	Post-process script failed."
fi

#while true; do
#    read -p "Would you like to remove the hosts list for this scan?" yn
#    case $yn in
#        [Yy]* ) make install; break;;
#        [Nn]* ) echo "";;
#        * ) echo "Please answer yes or no.";;
#    esac
#done
