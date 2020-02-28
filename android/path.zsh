### Android Studio for React Native
if [ -x /usr/libexec/java_home ]; then
  export JAVA_HOME=`/usr/libexec/java_home`
  export PATH=\"$JAVA_HOME/bin:$PATH\"
fi

export ANDROID_SDK=$HOME/Library/Android/sdk
export ANDROID_HOME=$ANDROID_SDK
export PATH=$ANDROID_SDK/emulator:$ANDROID_SDK/tools:$ANDROID_SDK/tools/bin:$PATH
export PATH=$ANDROID_SDK/platform-tools:$PATH

alias adb-reverse='adb reverse tcp:8080 tcp:8080 && adb reverse tcp:8081 tcp:8081'
