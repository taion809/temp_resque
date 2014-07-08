# -*- mode: ruby -*-
# vi: set ft=ruby :

# Vagrantfile API/syntax version. Don't touch unless you know what you're doing!
VAGRANTFILE_API_VERSION = "2"

Vagrant.configure(VAGRANTFILE_API_VERSION) do |config|
  config.vm.box = "trusty64"
  config.vm.network "forwarded_port", guest: 80, host: 8080
  config.vm.provider "virtualbox" do |vb|
     vb.customize ["modifyvm", :id, "--natdnshostresolver1", "on"]
  end

  config.vm.provision :shell, :inline => "apt-get update && apt-get upgrade -y"
  config.vm.provision :shell, :inline => "apt-get install -y curl vim git php5-fpm php5-cli php5-common php5-mcrypt php5-json php5-intl php5-curl nginx"
  config.vm.provision :shell, :inline => "curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer"
end
