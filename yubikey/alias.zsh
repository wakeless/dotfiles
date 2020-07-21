function otp() {
  if [ "$1" == "" ]; then
    SERVICE=$(ykman oath list | fzf | tr -d '\n')
    otp $SERVICE
  else
    echo "ykman oath code $1 -s | tr -d '\n' | pbcopy"
    ykman oath code $1 -s | pbcopy
    echo "OTP for $1 is in your clipboard!"
  fi
}
