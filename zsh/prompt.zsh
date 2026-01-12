autoload colors && colors
autoload -U promptinit
promptinit

directory_name() {
  echo "%{$fg_bold[cyan]%}%1/%\/%{$reset_color%}"
}

battery_status() {
  $ZSH/bin/battery-status -z -p
}

prompt_prefix() {
  if [ "$CODESPACES" == 'true' ]; then
    echo -n "\033[0;32m@${GITHUB_USER}\033[0m"
  else
    battery_status
  fi
}

set_prompt () {
  export PROMPT=$'$(prompt_prefix) in $(directory_name) › '
  export RPROMPT=$'$(vcs_prompt)'
}

precmd() {
  title "zsh" "%m" "%55<...<%~"
  set_prompt
}
