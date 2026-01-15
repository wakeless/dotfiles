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
    jj log --ignore-working-copy --no-graph --color never --revisions @ --template '
      separate(
        " ",
        coalesce(
          surround("%F{8}\"", "\"%f", truncate_end(24, description.first_line(), "...")),
          surround("%B%F{11}", "%f%b", description_placeholder)
        ),
        surround("%F{#a6da95}", "%f", "+") ++ surround("%F{#a6da95}", "%f", self.diff().stat().total_added()),
        surround("%F{#ed8796}", "%f", "-") ++ surround("%F{#ed8796}", "%f", self.diff().stat().total_removed()),
        surround("%B%F{#c6a0f6}", "%f%b", change_id.shortest(4).prefix()) ++ surround("%F{#5b6078}", "%f", change_id.shortest(4).rest()),
        surround("%B%F{#7dc4e4}", "%f%b", commit_id.shortest(8).prefix()) ++ surround("%F{#5b6078}", "%f", commit_id.shortest(8).rest()),
        surround("%F{#f0c6c6}", "%f", bookmarks.join(" ")),
        if(self.contained_in("visible_heads() & first_parent(@)"), surround("%F{#8bd5ca}", "%f", "git head")),
        if(conflict, surround("%F{#ed8796}", "%f", "(conflict)")),
        if(empty, "(empty)"),
        if(immutable, "(immutable)"),
        if(divergent, "(divergent)"),
        if(hidden, "(hidden)"),
      )
    '
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
