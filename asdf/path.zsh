# asdf shims - only add to PATH if asdf data dir exists
[[ -d "${ASDF_DATA_DIR:-$HOME/.asdf}" ]] && export PATH="${ASDF_DATA_DIR:-$HOME/.asdf}/shims:$PATH"
