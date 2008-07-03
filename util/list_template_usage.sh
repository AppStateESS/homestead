#!/bin/bash

for i in $(ls -R1 templates/ | grep tpl); do 
    echo "*** $i ***" && grep -inR $i class/* ;
done >> unused_templates.txt
