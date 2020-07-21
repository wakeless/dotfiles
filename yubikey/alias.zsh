otp() {
  name="${1:-$(ykman oath list | fzf)}"
  ykman oath code $name -s | tr -d '\n' | pbcopy
  echo "OTP for $name is in your clipboard!"
}
