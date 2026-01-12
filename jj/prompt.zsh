# VCS prompt for RPROMPT - jj (Jujutsu) or git
# jj prompt adapted from https://github.com/plasticine/dotfiles

jj_repo() {
  jj root --quiet &> /dev/null
}

jj_prompt() {
  if ! jj_repo; then
    return
  fi

  echo -e "$(
    jj log --ignore-working-copy --no-graph --color never --revisions @ --template "
      separate(
        ' ',
        coalesce(
          dim(surround(
            '\"',
            '\"',
            truncate_end(24, description.first_line(), '...')
          )),
          label(if(empty, 'empty'), bold(color('11', description_placeholder)))
        ),
        hex('#a6da95', '+') ++ hex('#a6da95', self.diff().stat().total_added()),
        hex('#ed8796', '-') ++ hex('#ed8796', self.diff().stat().total_removed()),
        hex('#c6a0f6', bold(change_id.shortest(4).prefix())) ++ hex('#5b6078', change_id.shortest(4).rest()),
        hex('#7dc4e4', bold(commit_id.shortest(8).prefix())) ++ hex('#5b6078', commit_id.shortest(8).rest()),
        hex('#f0c6c6', bookmarks.join(' ')),
        if(git_head, label('git_head', hex('#8bd5ca', 'git head'))),
        if(conflict, label('conflict', hex('#ed8796', '(conflict)'))),
        if(empty, label('empty', '(empty)')),
        if(immutable, '(immutable)'),
        if(divergent, '(divergent)'),
        if(hidden, '(hidden)'),
      )
    "
  )"
}

# Git prompt for RPROMPT (fallback when not in jj repo)
git_rprompt() {
  git rev-parse --is-inside-work-tree &>/dev/null || return

  local branch=$(git symbolic-ref HEAD 2>/dev/null | awk -F/ '{print $NF}')
  [[ -z "$branch" ]] && return

  local dirty=""
  [[ -n $(git status --porcelain 2>/dev/null) ]] && dirty="%F{red}*"

  local ahead=$(git log --oneline @{u}.. 2>/dev/null | wc -l | tr -d ' ')
  local behind=$(git log --oneline ..@{u} 2>/dev/null | wc -l | tr -d ' ')
  local sync=""
  [[ $ahead -gt 0 ]] && sync+="%F{magenta}${ahead}↑"
  [[ $behind -gt 0 ]] && sync+="%F{cyan}${behind}↓"

  print -n "%F{green}${branch}%f${dirty}"
  [[ -n $sync ]] && print -n " ${sync}"
  print -n "%f"
}

# Main VCS prompt - tries jj first, falls back to git
vcs_prompt() {
  local jj_output=$(jj_prompt)
  if [[ -n "$jj_output" ]]; then
    print -n "$jj_output"
  else
    git_rprompt
  fi
}
