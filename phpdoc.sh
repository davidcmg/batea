#!/bin/bash
# Scrit para generar los documentos con phpDocumentor.
# $ phive install --force-accept-unsigned phpDocumentor
# php tools/phpDocumentor
#
# Para generar gráficos es necesario instalar
# apt-get update && apt-get install -y graphviz plantuml
# sudo ln -s /usr/bin/plantuml /bin/plantuml


$HOME/tools/phpDocumentor run -d ./ -i config/Config.php -i vendor/ -i tests/ -t docs/ --cache-folder $HOME/phpdocs/cache --title 'BATEA Backup' --sourcecode --force -v --setting=graphs.enabled=true

