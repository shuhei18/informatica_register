let countdown = setInterval(function(){
    const now = new Date()  //今の日時
    const target = new Date("2024/9/13 13:00:00") //ターゲット日時を取得
    const remainTime = target - now  //差分を取る（ミリ秒で返ってくる

    //指定の日時を過ぎていたら処理をしない
    if(remainTime < 0) return false 

    //差分の日・時・分・秒を取得
    const difDay  = Math.floor(remainTime / 1000 / 60 / 60 / 24)
    const difHour = Math.floor(remainTime / 1000 / 60 / 60 ) % 24
    const difMin  = Math.floor(remainTime / 1000 / 60) % 60
    const difSec  = Math.floor(remainTime / 1000) % 60

    //残りの日時を上書き
    document.getElementById("days").textContent  = difDay
    document.getElementById("hours").textContent = difHour
    document.getElementById("minutes").textContent  = difMin
    document.getElementById("secounds").textContent  = difSec

    //指定の日時になればカウントを止める
    if(remainTime < 0) clearInterval(countdown)

}, 1000)    //1秒間に1度処