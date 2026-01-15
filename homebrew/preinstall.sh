#!/bin/sh
#
# Homebrew
#
# This installs Homebrew on macOS and Linux.
# See: https://docs.brew.sh/Homebrew-on-Linux

# Check for Homebrew
if test ! $(which brew)
then
  echo "  Installing Homebrew for you."
  /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

  # Set up PATH for the rest of this script
  if test -f /opt/homebrew/bin/brew; then
    eval "$(/opt/homebrew/bin/brew shellenv)"
  elif test -f /usr/local/bin/brew; then
    eval "$(/usr/local/bin/brew shellenv)"
  elif test -f /home/linuxbrew/.linuxbrew/bin/brew; then
    eval "$(/home/linuxbrew/.linuxbrew/bin/brew shellenv)"
  fi
fi

exit 0
