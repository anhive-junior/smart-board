# smart-board

## Getting Started - 시작하기

- 라즈베리파이 3b에 라즈비안 설치하기

## Prerequisites - 라즈비안 환경

- OS 환경 : RPi_stretch 이상

## installing - 설치하기

### git clone, - 클론으로 스마트 액자 프로그램 다운로드

```
cd ~
git clone https://github.com/anhive-junior/smart-board
```

### auto installing, - 자동 설치 스크립트 실행

```
cd ~/smart-board/.installer/
bash install_strech.sh
```

#### 라즈비안 기본설정

```
--------step.1 to set personalization------continue Y/n
```

Y : raspi-config 명령어를 불러들임.\
N : 다음 설정으로 넘어가기.

#### wifi 연결

```
--------step.2 to set WiFi connections------continue Y/n
```

Y : 와이파이를 연결함. - 와이파이에 대한 ssid와 password를 알고 있어야합니다.\
N : 다음 설정으로 넘어가기.

#### 라즈비안 프로그램 저장소 설정

```
--------step.3 to set RASPBIAN mirror------continue Y/n
```

Y : 저장소 위치를 Kaist (과학기술원)으로 변경합니다.\
N : 다음 설정으로 넘어가기.


#### SAMBA 프로그램 설치

```
--------step.4 to install file share(SAMBA)------continue Y/n
```

Y : SAMBA 프로그램을 설치합니다.\
N : 다음 설정으로 넘어가기.

#### apache 서버 설치 및 설정

```
------- step.5 to install web server(APACHE)------continue Y/n
```

Y : Apache 서버를 설치하고 설정합니다.\
N : 다음 설정으로 넘어가기.

#### 디지털 액자 관련 프로그램 설치

```
------- step.6 to install multimedia applicatiopn(FEH)------continue Y/n
```

Y : feh, omxplayer 프로그램 설치 및 설정\
N : 다음 설정으로 넘어가기.

#### 인터넷 액자 설치

```
------- step.7 to install Internet Photoframe(SurpriseBox)------continue Y/n
```

Y : 인터넷 액자 프로그램 설치 및 설정\
N : 다음 설정으로 넘어가기.

#### 기타 프로그램 설치

```
------- step.8 to install suppliment package(Utilities)------continue Y/n
```

Y : 인터넷 액자 관련 프로그램 설치 및 설정\
N : 다음 설정으로 넘어가기.

#### AP 프로그램 설치

```
------- step.9 to insall AP applications(HOSTAP)------continue Y/n
```

Y : 무선 wifi 프로그램 설치 및 설정\
N : 다음 설정으로 넘어가기.

#### AP 프로그램 모드를 설정

```
------- step.10 to change to AP mode and another setting------continue Y/n
```

Y : 무선 wifi 프로그램을 enable 합니다. - 다른 와이파이와 연결이 되지 않음. (인터넷을 사용하려면 LAN을 이용하여야함)\
N : 무선 wifi 프로그램을 disable 합니다. - 다른 와이파이와 연결하여 사용가능.

## 확인하기

smart-board 프로그램이 정상적으로 동작하는지 확인해보세요. 정상적으로 동작하지 않는다면, 다음과 같은 절차를 확인해주시길 바랍니다.

1. install.log 파일에서 설치하지 않은 프로그램 및 설치시 오류가 났는지 확인하기
2. 프로그램이 정상적으로 다 설치가 되었는데, smart-board에서 특정 기능이 동작하지 않는다면 이슈 사항에 올려주세요.
