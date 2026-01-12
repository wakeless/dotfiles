#!/usr/bin/env sh

if [ `uname` != "Linux" ]; then
    echo "Skipping. Only is run on Linux (not on Mac OS X)"; exit 0
fi

sudo apt-get install -y --no-install-recommends software-properties-common
sudo add-apt-repository ppa:x4121/ripgrep
sudo apt-get update
sudo apt-get install -y --no-install-recommends ripgrep bc hub

