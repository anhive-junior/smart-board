<p align="center">
<img src="https://user-images.githubusercontent.com/36920367/62415482-42ca0a00-b665-11e9-9b25-fa7f1614111d.png">
<img src="https://img.shields.io/badge/raspbian->=2019--06--20-red.svg">
<img src="https://img.shields.io/badge/php-%5E7.1.3-blue.svg">
<img src="https://img.shields.io/badge/smart--board-v0.05-orange.svg">
<img src="https://img.shields.io/badge/license-MIT-green.svg">
</p>

# smart-board

smart-board 프로젝트는 라즈베리파이로 집에서 혹은 가게에서 \
디지털 액자가 필요한 어느 곳이든지 적용할 수 있는 오픈소스입니다.

## 1. smart-board 적용 사례들

#### 1.1. 상점 (Store)

상점에서는 광고홍보용 사이니지 또는 식당에서 메뉴으로 사용되고 있습니다.

#### 1.2. 가정(with Family)

가정에서 사진을 전송하거나 보관할 때 사용하고 있습니다. \
여러 기능을 가지고 있어서 언제 어디서든 사진을 변경하고 올릴 수 있습니다. \
멀리 떨어져있는 가족에게 smart-board를 이용해서 바쁜 삶속에서 사진을 통하여 안부를 확인하는 역할도 할 수 있죠.

#### 1.3. 학교 (Education)

smart-board를 만드는 교육을 통하여 프로그램이 어떻게 만들어지는지에 대해서 배웁니다. \
쉬운 Copy & Paste 교육을 이용하기 때문에 어린 아이들도 쉽게 만들어볼 수 있습니다.

#### 1.4. 그 외의 것들 (Others)

무궁무진한 아이디어를 통하여 다양한 방면에 smart-board 오픈소스를 적용해보시길 바랍니다.

## smart-board 설치하기 (install smart-board)

smart-board를 설치하려면 라즈베리파이 OS인 라즈비안을 먼저 설치 하여야합니다.\
라즈비안이 설치가 되어있는 상태라면 밑에 'smart-board 설치하기' 링크에서 smart-board 설치하시길 바랍니다.

- [라즈비안 설치하기](https://github.com/anhive-junior/smart-board/wiki/%EB%9D%BC%EC%A6%88%EB%B9%84%EC%95%88-OS-%EC%84%A4%EC%B9%98)
- [smart-board 설치하기](https://github.com/anhive-junior/smart-board/wiki/Install-Smart-Board)

### 일괄 설치 프로그램 이용하기 (auto install smart-board)

#### [일괄 설치 프로그램](https://github.com/anhive-junior/smart-board/wiki/Smart-board-%EC%9D%BC%EA%B4%84-%EC%84%A4%EC%B9%98-%ED%94%84%EB%A1%9C%EA%B7%B8%EB%9E%A8)
자동으로 smart-board를 설치하려면 일괄 설치 프로그램을 이용하십시오. 일괄 설치 프로그램은 smart-board 프로그램을 한 스크립트로 모아서 설치하는 것으로 써, smart-board 프로그램을 빠르게 설치 해야할 시에 유용합니다. 단, 라즈비안 OS가 설치되어있어야 합니다.

## 기여 (Contribute)

smart-board 프로그램에 대한 문제점과 오류에 대해서 이슈사항에 올려주시거나 풀리퀘스트로 남겨주시길 바랍니다.

### code style - indent

코드를 수정 한 후 다음과 같은 명령어를 통해서 코드 들여쓰기 스타일을 수정해주시길 바랍니다.

```
#tab indent가 -> space 하나로 변경
find . -name "*.html" -exec sed -i 's/\t/    /g'  {} +
find . -name "*.php" -exec sed -i 's/\t/    /g'  {} +
```

## 만든이 (Author or Manager)

* **Jaeseok Ryu** - *github, @yhk1515* -
* **Sangha Lee** - *github, @toriato*-
* **Yonghoon Jung** - *github, @dydgns2017* -
* **Yongsoo Han** - *github, @ilovecho* -

## 라이센스 (License)

- [MIT_LICENSE](https://github.com/anhive-junior/smart-board/blob/master/LICENSE.md)
