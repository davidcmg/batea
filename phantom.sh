#!/bin/bash
# Ejecuta el navegador phantomjs para realizar los tests.
# A continuación se ejecuta codeception.
$HOME/phantomjs --webdriver=4444 &
codecept run --steps
