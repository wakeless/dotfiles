#!/usr/bin/env sh

if [ `uname` != "Linux" ]; then
    echo "Run on Linux (not on Mac OS X)"; exit 1
fi

sudo apt-get install software-properties-common
sudo add-apt-repository ppa:x4121/ripgrep
sudo apt-get update
sudo apt-get install -y --no-install-recommendsy ripgrep bc hub

