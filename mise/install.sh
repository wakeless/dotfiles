#!/usr/bin/env sh

# Install mise (https://mise.jdx.dev)
# This installs mise to ~/.local/bin/mise

if [ -f "$HOME/.local/bin/mise" ]; then
    echo "mise is already installed"
    exit 0
fi

echo "Installing mise..."
curl https://mise.run | sh

