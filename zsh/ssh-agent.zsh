# Load ssh keys into ssh-agent from keychain
eval $(ssh-agent) >/dev/null

for key in ~/.ssh/id_*(N); do
  [[ "$key" == *.pub ]] && continue
  ssh-add --apple-load-keychain "$key" 2>/dev/null
done
