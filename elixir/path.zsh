export PATH="$HOME/.mix/escripts:$PATH"

# for asdf elixir users
for escripts_dir in $(find "${ASDF_DATA_DIR:-$HOME/.asdf}/installs/elixir" -type d -name "escripts" 2>/dev/null); do
  export PATH="$escripts_dir:$PATH"
done
