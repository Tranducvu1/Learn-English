/**
 * Spaced Repetition (SRS) — SM-2 rút gọn
 * Gán window.SRS để luôn có global, kể cả thứ tự script lệch.
 */
(function (global) {
  'use strict';

  var intervals = [1, 3, 7, 14, 30, 60];

  function initCard(wordId) {
    return {
      wordId: wordId,
      ease: 2.5,
      interval: 0,
      repetitions: 0,
      nextReview: Date.now(),
      lastReview: null
    };
  }

  function getCard(wordId, srsCards) {
    if (!srsCards[wordId]) {
      srsCards[wordId] = initCard(wordId);
    }
    return srsCards[wordId];
  }

  /** quality: 0=again, 1=hard, 2=good, 3=easy */
  function review(card, quality) {
    var now = Date.now();
    card.lastReview = now;

    if (quality === 0) {
      card.repetitions = 0;
      card.interval = 0;
      card.nextReview = now + 60000;
      return card;
    }

    if (quality === 1) {
      var easeVal = Number(card.ease);
      if (isNaN(easeVal)) easeVal = 2.5;
      card.ease = Math.max(1.3, easeVal - 0.15);
      card.interval = Math.max(1, Math.floor(card.interval * 0.5));
    } else if (quality === 2) {
      card.repetitions += 1;
      card.interval = intervals[Math.min(card.repetitions - 1, intervals.length - 1)];
    } else {
      card.repetitions += 1;
      card.ease += 0.1;
      card.interval = intervals[Math.min(card.repetitions, intervals.length - 1)] * 2;
    }

    card.nextReview = now + card.interval * 24 * 60 * 60 * 1000;
    return card;
  }

  function getDueCards(vocabWords, srsCards) {
    var now = Date.now();
    return vocabWords.filter(function (w) {
      var c = srsCards[w.id];
      if (!c) return true;
      return c.nextReview <= now;
    });
  }

  function getStats(srsCards) {
    var cards = Object.values(srsCards);
    var now = Date.now();
    return {
      total: cards.length,
      due: cards.filter(function (c) { return c.nextReview <= now; }).length,
      mastered: cards.filter(function (c) { return c.repetitions >= 4; }).length
    };
  }

  global.SRS = {
    intervals: intervals,
    initCard: initCard,
    getCard: getCard,
    review: review,
    getDueCards: getDueCards,
    getStats: getStats
  };
})(typeof window !== 'undefined' ? window : this);
