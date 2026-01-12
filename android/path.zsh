if [ -x /usr/libexec/java_home ] && /usr/libexec/java_home &>/dev/null; then
  export JAVA_HOME=$(/usr/libexec/java_home)
  export PATH=$JAVA_HOME/bin:$PATH
fi

if [ -z ${ANDROID_SDK_ROOT+x} ]; then
  export ANDROID_SDK_ROOT=$HOME/Library/Android/sdk
  export PATH=$ANDROID_SDK_ROOT/emulator:$PATH
  export PATH=$ANDROID_SDK_ROOT/tools:$PATH
  export PATH=$ANDROID_SDK_ROOT/tools/bin:$PATH
  export PATH=$ANDROID_SDK_ROOT/platform-tools:$PATH
fi

alias adb-reverse='adb reverse tcp:8080 tcp:8080 && adb reverse tcp:8081 tcp:8081'
