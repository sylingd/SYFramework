#!/bin/bash

installExt() {
	stage=$(mktemp -d)

	cd $stage
	wget -O ${1}.tar.gz https://github.com/${2}/archive/${3}.tar.gz
	tar -zxf ${1}.tar.gz
	cd ${4}
	phpize
	./configure ${5}
	make -j4
	sudo make install
	if [[ -f "$TRAVIS_BUILD_DIR/ci/config/${1}.ini" ]];then
		phpenv config-add "$TRAVIS_BUILD_DIR/ci/config/${1}.ini"
	fi

	cd $TRAVIS_BUILD_DIR
	sudo rm -rf $stage
}

main() {
	redis_ver="0.14.0"
	yac_ver="2.0.2"
	yaconf_ver="1.0.7"
	memcached_ver="3.1.3"

	# Install redis
	installExt "redis" "phpredis/phpredis" "${redis_ver}" "phpredis-${redis_ver}"

	# Install memcached
	sudo add-apt-repository ppa:ubuntu/libmemcached
	sudo apt-get update
	sudo apt-get install -y libmemcached libmemcached11 libmemcached-dev libmemcachedutil2 libhashkit2 libhashkit-dev
	installExt "memcached" "php-memcached-dev/php-memcached" "v${memcached_ver}" "php-memcached-${memcached_ver}"

	# Install Yac
	can_install_yac=$(php -r "echo version_compare(PHP_VERSION, '7.3');")
	if [[ "$can_install_yac" == "-1" ]];then
		installExt "yac" "laruence/yac" "yac-${yac_ver}" "yac-yac-${yac_ver}"
	else
		echo -e "Skip install Yac\n"
	fi

	# Install Yaconf
	installExt "yaconf" "laruence/yaconf" "yaconf-${yaconf_ver}" "yaconf-yaconf-${yaconf_ver}"
}

main