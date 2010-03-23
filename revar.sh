#!/bin/bash

# Version/Date
version=1.1
today=`date "+%B %d, %Y"`

# Date Changer
grep -ilr --exclude-dir=.git --exclude=revar.sh --exclude=README.md "[DATE]" * | xargs -i@ sed -i "s/\[DATE\]/${today}/g" @

# Version Changer
grep -ilr --exclude-dir=.git --exclude=revar.sh --exclude=README.md "[VERSION]" * | xargs -i@ sed -i "s/\[VERSION\]/${version}/g" @
