for i in {1..213}
do
	if [ $i -lt 10 ]; then
		file="00$i"
	elif [ $i -lt 100 ]; then
		file="0$i"
	else
		file=$i
	fi

	curl "http://csszengarden.com/$file/$file.css" -o "csszengarden.com.$file.css"
	if cat "csszengarden.com.$file.css" | grep -q "<title>404 Not Found</title>"
	then
		rm "csszengarden.com.$file.css"
	fi
done
