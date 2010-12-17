cd `dirname $0`

# Drop all error files
ls -1 errors/*.css > /dev/null 2>&1
if [ "$?" = "0" ]; then
	rm errors/*.css
fi


# Drop all dist directorys
ls -1 benchmark/dist/temp-* > /dev/null 2>&1
if [ "$?" = "0" ]; then
	rm -rf benchmark/dist/temp-*
fi


# Remove temp json files
ls -1 benchmark/results/temp-* > /dev/null 2>&1
if [ "$?" = "0" ]; then
	rm benchmark/results/temp-*
fi


# Remove last run file
if [ -e benchmark/dist/lastrun.txt ]; then
	rm benchmark/dist/lastrun.txt
fi
