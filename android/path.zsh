### Android Studio for React Native
if [ -x /usr/libexec/java_home ]; then
  export JAVA_HOME=`/usr/libexec/java_home`
  export PATH=\"$JAVA_HOME/bin:$PATH\"
fi
export ANDROID_HOME=$HOME/Library/Android/sdk
export PATH=$PATH:$ANDROID_HOME/tools
export PATH=$PATH:$ANDROID_HOME/platform-tools

alias adb-reverse='adb reverse tcp:8080 tcp:8080 && adb reverse tcp:8081 tcp:8081'
alias emulator=${ANDROID_HOME}/emulator/emulator
