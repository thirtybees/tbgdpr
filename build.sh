#!/usr/bin/env bash
CWD_BASENAME=${PWD##*/}
CWD_BASEDIR=${PWD}
echo ${CWD_BASEDIR}
echo "Building the GDPR module"
echo "Create packaging directory"

# Cleanup before scoping
rm pre-scoper/ -rf
rm vendor/ -rf
rm build/ -rf

# Composer install and scoping
composer install --no-dev --prefer-dist
mv vendor/ pre-scoper/
php ./php-scoper.phar add-prefix -p TbGdprModule -n

# Scoping cleanup
mv build/pre-scoper/ vendor/
rm pre-scoper/ -rf
rm build/ -rf

# Dump new autoloader
composer -o dump-autoload

DIR=$(mktemp -d)
export DIR

cp ${CWD_BASEDIR}/ ${DIR} -rf

# Webpack build
cd ${DIR}/${CWD_BASENAME}/views/js/src/

if [ -x "$(command -v yarn)" ]; then
  yarn install
else
  npm install
fi
rm -rf ${DIR}/${CWD_BASENAME}/views/js/dist/
NODE_ENV=production webpack --mode production
cp ${DIR}/${CWD_BASENAME}/views/js/index.php ${DIR}/${CWD_BASENAME}/views/js/dist/index.php

cd ${DIR}/${CWD_BASENAME}

# Zip file

FILES+=("logo.gif")
FILES+=("logo.png")
FILES+=("${CWD_BASENAME}.php")
FILES+=("index.php")
FILES+=("classes/**")
FILES+=("controllers/**")
FILES+=("mails/en/**")
FILES+=("mails/index.php")
FILES+=("sql/**")
FILES+=("traits/**")
FILES+=("translations/**")
FILES+=("upgrade/**")
FILES+=("vendor/**")
FILES+=("views/css/**")
FILES+=("views/js/configtabs.js")
FILES+=("views/js/consent-modal.js")
FILES+=("views/js/cookieconsent.min.js")
FILES+=("views/js/popover.js")
FILES+=("views/js/sweetalert.min.js")
FILES+=("views/index.php")
FILES+=("views/js/index.php")
FILES+=("views/js/index.php")
FILES+=("views/js/dist/index.php")
FILES+=("views/js/dist/export.bundle.min.js")
FILES+=("views/js/dist/requests.bundle.min.js")
FILES+=("views/js/dist/translations.bundle.min.js")
FILES+=("views/templates/**")

MODULE_VERSION="$(sed -ne "s/\\\$this->version *= *['\"]\([^'\"]*\)['\"] *;.*/\1/p" ${CWD_BASENAME}.php)"
MODULE_VERSION=${MODULE_VERSION//[[:space:]]}
ZIP_FILE="${CWD_BASENAME}/${CWD_BASENAME}-v${MODULE_VERSION}.zip"

echo "Going to zip ${CWD_BASENAME} version ${MODULE_VERSION}"

cd ..
rm ${ZIP_FILE}
for E in "${FILES[@]}"; do
  find ${CWD_BASENAME}/${E}  -type f -exec zip -9 ${ZIP_FILE} {} \;
done
cp ${DIR}/${ZIP_FILE} ${CWD_BASEDIR}
