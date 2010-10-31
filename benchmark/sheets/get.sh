for i in {1..213}
do
	if [ $i -lt 10 ]; then
		file="00$i"
	elif [ $i -lt 100 ]; then
		file="0$i"
	else
		file=$i
	fi

	curl "http://csszengarden.com/$file/$file.css" -o "$file.css"
done
