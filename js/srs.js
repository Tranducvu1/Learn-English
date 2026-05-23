/**
 * Spaced Repetition System (SRS) cơ bản — SM-2 simplified
 */
const SRS = {
  intervals: [1, 3, 7, 14, 30, 60],

  initCard(wordId) {
    return {
      wordId,
      ease: 2.5,
      interval: 0,
      repetitions: 0,
      nextReview: Date.now(),
      lastReview: null
    };
  },

  getCard(wordId, srsCards) {
    if (!srsCards[wordId]) {
      srsCards[wordId] = this.initCard(wordId);
    }
    return srsCards[wordId];
  },

  /** quality: 0=again, 1=hard, 2=good, 3=easy */
  review(card, quality) {
    const now = Date.now();
    card.lastReview = now;

    if (quality === 0) {
      card.repetitions = 0;
      card.interval = 0;
      card.nextReview = now + 60000;
      return card;
    }

    if (quality === 1) {
      card.ease = Math.max(1.3, card.ea se - 0.15);
      card.interval = Math.max(1, Math.floor(card.interval * 0.5));
    } else if (quality === 2) {
      card.repetitions += 1;
      card.interval = this.intervals[Math.min(card.repetitions - 1, this.intervals.length - 1)];
    } else {
      card.repetitions += 1;
      card.ease += 0.1;
      card.interval = this.intervals[Math.min(card.repetitions, this.intervals.length - 1)] * 2;
    }

    card.nextReview = now + card.interval * 24 * 60 * 60 * 1000;
    return card;
  },

  getDueCards(vocabWords, srsCards) {
    const now = Date.now();
    return vocabWords.filter(w => {
      const card = srsCards[w.id];
      if (!card) return true;
      return card.nextReview <= now;
    });
  },

  getStats(srsCards) {
    const cards = Object.values(srsCards);
    return {
      total: cards.length,
      due: cards.filter(c => c.nextReview <= Date.now()).length,
      mastered: cards.filter(c => c.repetitions >= 4).length
    };
  }
};
