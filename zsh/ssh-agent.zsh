#
###
## Alternative to gnome-keyring to load ssh keys into ssh-agent.
##
## $ source .ssh/ssh-agent
###
#
eval $(ssh-agent)
#
KEYS="dsa rsa"
for key in $KEYS ; do
  if [ -f "~/.ssh/id_$key" ] ; then
      ssh-add ~/.ssh/id_$key
  fi
done
