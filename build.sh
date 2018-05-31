#!/usr/bin/env bash
CWD_BASENAME=${PWD##*/}
CWD_BASEDIR=${PWD}
echo ${CWD_BASEDIR}
BUILD_HASH="$(openssl rand -hex 8)"
echo "Building the GDPR module"
echo "Create packaging directory"
echo "Build hash: ${BUILD_HASH}"

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
cd ${DIR}/${CWD_BASENAME}/views/js/app/

if [ -x "$(command -v yarn)" ]; then
  yarn install
else
  npm install
fi
rm -rf ${DIR}/${CWD_BASENAME}/views/js/app/dist/
NODE_ENV=production webpack --mode production
cp ${DIR}/${CWD_BASENAME}/views/js/app/index.php ${DIR}/${CWD_BASENAME}/views/js/app/dist/index.php
cp ${DIR}/${CWD_BASENAME}/views/js/app/dist/checkout-__BUILD_HASH__.bundle.min.js ${DIR}/${CWD_BASENAME}/views/js/app/dist/checkout-${BUILD_HASH}.bundle.min.js
cp ${DIR}/${CWD_BASENAME}/views/js/app/dist/ordergrid-__BUILD_HASH__.bundle.min.js ${DIR}/${CWD_BASENAME}/views/js/app/dist/ordergrid-${BUILD_HASH}.bundle.min.js
cp ${DIR}/${CWD_BASENAME}/views/js/app/dist/orderpage-__BUILD_HASH__.bundle.min.js ${DIR}/${CWD_BASENAME}/views/js/app/dist/orderpage-${BUILD_HASH}.bundle.min.js
cp ${DIR}/${CWD_BASENAME}/views/js/app/dist/paperselector-__BUILD_HASH__.bundle.min.js ${DIR}/${CWD_BASENAME}/views/js/app/dist/paperselector-${BUILD_HASH}.bundle.min.js

cd ${DIR}/${CWD_BASENAME}

# Zip file
find . -type f -name '*.php' -exec sed -i "s/__BUILD_HASH__/${BUILD_HASH}/g" {} \;
find . -type f -name '*.tpl' -exec sed -i "s/__BUILD_HASH__/${BUILD_HASH}/g" {} \;

FILES+=("logo.gif")
FILES+=("logo.png")
FILES+=("${CWD_BASENAME}.php")
FILES+=("index.php")
FILES+=("classes/**")
FILES+=("controllers/**")
FILES+=("mails/**")
FILES+=("sql/**")
FILES+=("traits/**")
FILES+=("translations/**")
FILES+=("upgrade/**")
FILES+=("vendor/**")
FILES+=("views/css/**")
FILES+=("views/**")

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
