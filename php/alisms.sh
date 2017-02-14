#!/usr/bin/env bash

RECEIVER=13764166229     #接收方手机号
SIGN="爱车师傅"            #签名
TEMP_CODE="SMS_22425014"      #短信模板
PARAMS="{\"msg\":\"12342\"}" #模板参数（json格式）

K="23492367 " #AppKey，从管理控制台获取，下同
S="b424103e3b546e850f3c80f74c45f4d6" #AppSecret

NL="
"
[ "x`uname`" = "xDarwin" ] && {
NONCE="`uuidgen`"
TIMESTAMP="`date +%s`500"
} || {
NONCE="`uuid`"
TIMESTAMP="`date +%s%3N`"
}
STR_HEADER="X-Ca-Key:$K${NL}X-Ca-Nonce:$NONCE${NL}X-Ca-Timestamp:$TIMESTAMP"
STR_URI="/singleSendSms?ParamString=$PARAMS&RecNum=$RECEIVER&SignName=$SIGN&TemplateCode=$TEMP_CODE"
STR_TO_SIGN="GET${NL}${NL}${NL}${NL}${NL}$STR_HEADER${NL}$STR_URI"
SIGN="`/bin/echo -n "$STR_TO_SIGN" | openssl dgst -sha256 -hmac "$S" | sed 's/.* //g' | xxd -r -p | base64`"
STR_URI="`echo "$STR_URI" | sed 's#{#\\\\{#g;s#}#\\\\}#g'`"
curl -v -H 'Accept:' \
    -H "X-Ca-Key: $K" \
    -H "X-Ca-Nonce: $NONCE" \
    -H "X-Ca-Timestamp: $TIMESTAMP" \
    -H "X-Ca-Signature-Headers: X-Ca-Key,X-Ca-Nonce,X-Ca-Timestamp" \
    -H "X-Ca-Signature: $SIGN" \
    "http://sms.market.alicloudapi.com$STR_URI"