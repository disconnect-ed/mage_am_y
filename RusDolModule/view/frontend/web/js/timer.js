define(['uiComponent', 'ko'], function (Component, ko) {
    return Component.extend({
        defaults: {
            seconds: 0,
            minutes: 0,
            hours: 0,
            secondsMain: '',
            minutesMain: '',
            hoursMain: '',
            timerInterval: -1,
            timerState: '00:00:00'
        },
        initObservable: function () {
            this._super();
            this.observe(['hoursMain', 'minutesMain', 'secondsMain', 'timerState']);
            return this;
        },
        displayTimer: function () {
            this.seconds++;
            if (this.seconds >= 60) {
                this.minutes++;
                this.seconds = 0;
            }
            if (this.minutes >= 60) {
                this.hours++;
                this.minutes = 0;
            }
            this.hoursMain = this.hours;
            this.minutesMain = this.minutes;
            this.secondsMain = this.seconds;
            if (this.hours < 10) {
                this.hoursMain = '0' + this.hours;
            }
            if (this.minutes < 10) {
                this.minutesMain = '0' + this.minutes;
            }
            if (this.seconds < 10) {
                this.secondsMain = '0' + this.seconds;
            }
            this.timerState(this.hoursMain + ':' + this.minutesMain + ':' + this.secondsMain);
        },
        startTimer: function () {
            if (this.timerInterval == -1) {
                this.timerInterval = setInterval(function () {
                    this.displayTimer();
                }.bind(this), 1000);
            }
        },
        stopTimer: function () {
            this.clearTimer();
            clearInterval(this.timerInterval);
            this.timerInterval = -1;
            this.timerState('00:00:00');
        },
        pauseTimer: function () {
            if (this.timerInterval != -1) {
                clearInterval(this.timerInterval);
                this.timerInterval = -1;
            }
        },
        clearTimer: function () {
            this.hours = 0;
            this.minutes = 0;
            this.seconds = 0;
            this.hoursMain = '';
            this.minutesMain = '';
            this.secondsMain = '';
        }
    });
});