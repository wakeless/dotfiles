# asdf completions
if (( $+commands[brew] )) && (( $+commands[asdf] )); then
  local asdf_completions="$(brew --prefix asdf 2>/dev/null)/etc/bash_completion.d/asdf"
  [[ -f "$asdf_completions" ]] && source "$asdf_completions"
fi
