ZSH_THEME_GIT_PROMPT_PREFIX="("
ZSH_THEME_GIT_PROMPT_SUFFIX=")"
ZSH_THEME_GIT_PROMPT_DIRTY="%{$fg[red]%}✗%{$fg[blue]%}"
ZSH_THEME_GIT_PROMPT_CLEAN="%{$fg[green]%}✔%{$fg[blue]%}"
ZSH_THEME_HUB_CI_PROMPT_SUCCESS="%{$fg[green]%}Success ●%{$fg[blue]%}"
ZSH_THEME_HUB_CI_PROMPT_PENDING="%{$fg[yellow]%}Pending ●%{$fg[blue]%}"
ZSH_THEME_HUB_CI_PROMPT_FAIL="%{$fg[red]%}Fail ●%{$fg[blue]%}"
ZSH_THEME_HUB_CI_PROMPT_NO_STATUS=""

hub_ci_status() {
  # If rate limiting is important, we may not want to call this every time
  # local CURRENT_TIME=''
  # CURRENT_TIME=$(date +%s 2> /dev/null | tail -n1)
  # if [[ $(($CURRENT_TIME % 2)) = 0 ]]; then
  local STATUS=''
  STATUS=$(command hub ci-status 2> /dev/null | tail -n1)
  REF=$(command git rev-parse --short HEAD 2> /dev/null) || return 0

  if [[ $STATUS = 'success' ]]; then
    echo "$ZSH_THEME_HUB_CI_PROMPT_SUCCESS"
  elif [[ $STATUS = 'pending' ]]; then
    echo "$ZSH_THEME_HUB_CI_PROMPT_PENDING"
  elif [[ $STATUS = 'no status' ]]; then
    echo "$ZSH_THEME_HUB_CI_PROMPT_NO_STATUS"
  else
    echo "$ZSH_THEME_HUB_CI_PROMPT_FAIL"
  fi
  # fi
}

