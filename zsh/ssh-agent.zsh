#
###
## Alternative to gnome-keyring to load ssh keys into ssh-agent.
##
## $ source .ssh/ssh-agent
###
#
eval $(ssh-agent)
#
for key in `ls ~/.ssh/id_* | grep -v .pub$` ; do
  ssh-add $key
done
