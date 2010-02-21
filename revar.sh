#!/bin/bash

# Version/Date
version=1.3
today=`date "+%B %d, %Y"`

# Date Changer
grep -ilr --exclude-dir=.git --exclude=revar.sh "[DATE]" * | xargs -i@ sed -i "s/\[DATE\]/${today}/g" @

# Version Changer
grep -ilr --exclude-dir=.git --exclude=revar.sh "[VERSION]" * | xargs -i@ sed -i "s/\[VERSION\]/${version}/g" @
