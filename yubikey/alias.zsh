otp() {
  name="${1:-$(ykman oath list | fzf)}"
  ykman oath code $name -s | tr -d '\n' | pbcopy
  echo "OTP for $name is in your clipboard!"
}

otp-add() {
  ykman oath add $1 -t
  otp $1
}
