/**
 * Sinh data/lessons.json — bài học HSK 1-6 (nhiều bài, gắn từ vựng thật)
 * node scripts/generate-lessons.cjs
 */
const fs = require('fs');
const path = require('path');
const { dataDir } = require('./paths.cjs');

const VOCAB_PATH = path.join(dataDir, 'vocabulary.json');
const { TOPICS, TOPIC_NAMES, dominantTopic, groupByTopic } = require('./topic-rules.cjs');

const LEVEL_META = {
  hsk1: { name: 'HSK 1', color: '#22c55e', num: 1, desc: '150 từ — chào hỏi, gia đình, số đếm, giao tiếp cơ bản', chunk: 6, minLessons: 10 },
  hsk2: { name: 'HSK 2', color: '#3b82f6', num: 2, desc: '300 từ — mua sắm, ăn uống, đi lại, giao tiếp hàng ngày', chunk: 8, minLessons: 14 },
  hsk3: { name: 'HSK 3', color: '#8b5cf6', num: 3, desc: '600 từ — du lịch, sức khỏe, công việc, ôn thi', chunk: 8, minLessons: 14 },
  hsk4: { name: 'HSK 4', color: '#f59e0b', num: 4, desc: '1200 từ — văn phòng, xã hội, báo cáo, thi HSK', chunk: 10, minLessons: 14 },
  hsk5: { name: 'HSK 5', color: '#ec4899', num: 5, desc: '2500 từ — kinh tế, văn hóa, học thuật', chunk: 10, minLessons: 12 },
  hsk6: { name: 'HSK 6', color: '#dc2626', num: 6, desc: '5000+ từ — học thuật, hội thảo, chuyên sâu', chunk: 10, minLessons: 12 }
};

function lesson(id, title, topic, duration, vocabIds, skills, intro, dialogue) {
  return { id, title, topic, duration, vocabIds, skills, content: { intro, dialogue } };
}

const D = (speaker, hanzi, pinyin, vietnamese) => ({ speaker, hanzi, pinyin, vietnamese });

/** Bài có hội thoại tay — ưu tiên hiển thị trước */
const MANUAL = {
  hsk1: [
    ['hsk1-l01', 'Chào hỏi & Giới thiệu', 'giao-tiep', 15, ['你好', '谢谢', '再见', '我', '叫'],
      'Học chào hỏi và giới thiệu bản thân.',
      [D('A', '你好！', 'Nǐ hǎo!', 'Xin chào!'), D('B', '你好！我叫小明。', 'Nǐ hǎo! Wǒ jiào Xiǎo Míng.', 'Tôi tên Tiểu Minh.'), D('A', '很高兴认识你。', 'Hěn gāoxìng rènshi nǐ.', 'Rất vui được gặp bạn.')]],
    ['hsk1-l02', 'Hỏi thăm & Lịch sự', 'giao-tiep', 12, ['你', '好', '不', '客气'],
      'Giao tiếp lịch sự hàng ngày.',
      [D('A', '你好吗？', 'Nǐ hǎo ma?', 'Bạn khỏe không?'), D('B', '我很好，谢谢。', 'Wǒ hěn hǎo, xièxie.', 'Tôi khỏe, cảm ơn.')]],
    ['hsk1-l03', 'Gia đình', 'gia-dinh', 18, ['妈妈', '爸爸', '家', '孩子'],
      'Thành viên gia đình.',
      [D('A', '这是我的妈妈。', 'Zhè shì wǒ de māma.', 'Đây là mẹ tôi.'), D('B', '你家有几口人？', 'Nǐ jiā yǒu jǐ kǒu rén?', 'Nhà bạn mấy người?')]]
  ],
  hsk2: [
    ['hsk2-l01', 'Mua sắm & Trả giá', 'mua-sam', 20, ['买', '钱', '便宜', '多少', '贵'],
      'Hội thoại mua hàng và hỏi giá.',
      [D('A', '这个多少钱？', 'Zhège duōshao qián?', 'Bao nhiêu tiền?'), D('B', '可以便宜一点吗？', 'Kěyǐ piányi yīdiǎn ma?', 'Rẻ hơn được không?')]],
    ['hsk2-l02', 'Nhà hàng & Gọi món', 'an-uong', 22, ['吃', '菜', '服务员', '请', '茶'],
      'Gọi món tại nhà hàng.',
      [D('A', '服务员，请给我菜单。', 'Fúwùyuán, qǐng gěi wǒ càidān.', 'Cho tôi xem thực đơn.'), D('B', '好的，请稍等。', 'Hǎo de, qǐng shāo děng.', 'Vâng, xin đợi.')]],
    ['hsk2-l03', 'Thời tiết & Mùa', 'thoi-tiet', 18, ['天气', '热', '冷', '下雨', '太阳'],
      'Nói về thời tiết.',
      [D('A', '今天天气怎么样？', 'Jīntiān tiānqì zěnmeyàng?', 'Thời tiết hôm nay?'), D('B', '今天很热，会下雨。', 'Jīntiān hěn rè, huì xiàyǔ.', 'Hôm nay nóng, sẽ mưa.')]],
    ['hsk2-l04', 'Đi lại & Phương tiện', 'phuong-tien', 20, ['车', '去', '来', '站', '地铁'],
      'Hỏi đường và phương tiện.',
      [D('A', '地铁站在哪儿？', 'Dìtiě zhàn zài nǎr?', 'Trạm metro ở đâu?'), D('B', '往前走，然后左转。', 'Wǎng qián zǒu, ránhòu zuǒ zhuǎn.', 'Đi thẳng rồi rẽ trái.')]],
    ['hsk2-l05', 'Taxi & Xe buýt', 'phuong-tien', 18, ['出租车', '公共汽车', '票', '到达'],
      'Đi taxi và xe buýt.',
      [D('A', '请送我去机场。', 'Qǐng sòng wǒ qù jīchǎng.', 'Đưa tôi đến sân bay.'), D('B', '好的，请系安全带。', 'Hǎo de, qǐng jì ānquándài.', 'Vâng, thắt dây an toàn.')]]
  ],
  hsk3: [
    ['hsk3-l01', 'Đặt phòng khách sạn', 'du-lich', 25, ['旅游', '酒店', '房间', '想', '预订'],
      'Đặt phòng khách sạn.',
      [D('A', '我想订一个房间。', 'Wǒ xiǎng dìng yīgè fángjiān.', 'Tôi muốn đặt phòng.'), D('B', '住几晚？', 'Zhù jǐ wǎn?', 'Ở mấy đêm?')]],
    ['hsk3-l02', 'Hỏi đường du lịch', 'du-lich', 22, ['地图', '远', '近', '怎么', '博物馆'],
      'Hỏi đường khi du lịch.',
      [D('A', '博物馆怎么走？', 'Bówùguǎn zěnme zǒu?', 'Đi bảo tàng thế nào?'), D('B', '一直往前走。', 'Yìzhí wǎng qián zǒu.', 'Đi thẳng.')]],
    ['hsk3-l03', 'Bệnh viện & Sức khỏe', 'suc-khoe', 24, ['医院', '医生', '病', '疼', '药'],
      'Khám bệnh.',
      [D('A', '我头疼，想去看医生。', 'Wǒ tóu téng, xiǎng qù kàn yīshēng.', 'Tôi đau đầu, muốn gặp bác sĩ.')]],
    ['hsk3-l04', 'Sân bay & Máy bay', 'phuong-tien', 24, ['飞机', '机场', '护照', '行李'],
      'Tại sân bay.',
      [D('A', '我的航班几点起飞？', 'Wǒ de hángbān jǐ diǎn qǐfēi?', 'Chuyến bay mấy giờ cất cánh?')]],
    ['hsk3-l05', 'Siêu thị & Mua đồ', 'mua-sam', 20, ['超市', '买', '水果', '蔬菜'],
      'Mua sắm tại siêu thị.',
      [D('A', '这些水果新鲜吗？', 'Zhèxiē shuǐguǒ xīnxiān ma?', 'Trái cây này tươi không?')]]
  ],
  hsk4: [
    ['hsk4-l01', 'Văn phòng hàng ngày', 'cong-viec', 28, ['工作', '忙', '会议', '同事', '报告'],
      'Giao tiếp nơi làm việc.',
      [D('A', '我今天很忙，下午有会议。', 'Wǒ jīntiān hěn máng, xiàwǔ yǒu huìyì.', 'Hôm nay bận, chiều có họp.')]],
    ['hsk4-l02', 'Phỏng vấn xin việc', 'cong-viec', 30, ['面试', '经验', '公司', '希望', '简历'],
      'Phỏng vấn xin việc.',
      [D('A', '你为什么想加入我们公司？', 'Nǐ wèishénme xiǎng jiārù wǒmen gōngsī?', 'Vì sao muốn vào công ty?')]]
  ],
  hsk5: [
    ['hsk5-l01', 'Kinh tế & Thị trường', 'cong-viec', 32, ['经济', '市场', '发展', '竞争', '投资'],
      'Thảo luận kinh tế.',
      [D('A', '市场竞争很激烈。', 'Shìchǎng jìngzhēng hěn jīliè.', 'Cạnh tranh thị trường gay gắt.')]],
    ['hsk5-l02', 'Văn hóa & Lễ hội', 'van-hoa', 30, ['文化', '传统', '节日', '历史', '春节'],
      'Văn hóa Trung Hoa.',
      [D('A', '春节是最重要的节日。', 'Chūnjié shì zuì zhòngyào de jiérì.', 'Tết là lễ quan trọng nhất.')]],
    ['hsk5-l03', 'Công nghệ & Internet', 'cong-nghe', 28, ['网络', '手机', '电脑', '数据'],
      'Công nghệ thông tin.',
      [D('A', '现在大家都用手机上网。', 'Xiànzài dàjiā dōu yòng shǒujī shàngwǎng.', 'Mọi người đều lên mạng bằng điện thoại.')]]
  ],
  hsk6: [
    ['hsk6-l01', 'Học thuật & Luận văn', 'thi-hsk', 35, ['学术', '论文', '发表', '研究', '课题'],
      'Ngôn ngữ học thuật.',
      [D('A', '这篇论文很有深度。', 'Zhè piān lùnwén hěn yǒu shēndù.', 'Bài luận văn có chiều sâu.')]],
    ['hsk6-l02', 'Hội thảo chuyên đề', 'cong-viec', 35, ['讨论', '观点', '分析', '结论', '证据'],
      'Thảo luận chuyên đề.',
      [D('A', '我们从多个角度分析这个问题。', 'Wǒmen cóng duōgè jiǎodù fēnxī zhège wèntí.', 'Phân tích vấn đề từ nhiều góc độ.')]]
  ]
};

function makeDialogue(words) {
  const w0 = words[0];
  const w1 = words[1] || w0;
  const w2 = words[2] || w1;
  if (!w0) return [];
  return [
    D('A', `你知道“${w0.hanzi}”吗？`, `Nǐ zhīdào "${w0.hanzi}" ma?`, `Bạn biết “${w0.vietnamese}” không?`),
    D('B', `知道，${w1.hanzi}很重要。`, `Zhīdào, ${w1.hanzi} hěn zhòngyào.`, `Biết, “${w1.vietnamese}” rất quan trọng.`),
    D('A', `我们一起练习${w2.hanzi}。`, `Wǒmen yīqǐ liànxí ${w2.hanzi}.`, `Cùng luyện “${w2.vietnamese}” nhé.`)
  ];
}

function manualToLesson(row) {
  const [id, title, topic, duration, vocabIds, intro, dialogue] = row;
  return lesson(id, title, topic, duration, vocabIds,
    ['listen', 'read', 'speak'], intro, dialogue);
}

function autoLesson(levelId, hskNum, index, words, usedIds, forcedTopic) {
  const topic = forcedTopic || dominantTopic(words);
  const topicLabel = TOPIC_NAMES[topic] || topic;
  const n = String(index).padStart(2, '0');
  let id = `${levelId}-${topic}-${n}`;
  while (usedIds.has(id)) id = `${levelId}-${topic}-${n}x${index}`;
  usedIds.add(id);

  const vocabIds = words.map(w => w.hanzi);
  const hanziSample = vocabIds.slice(0, 3).join('、');
  return lesson(
    id,
    `${topicLabel} — ${hanziSample}`,
    topic,
    Math.min(35, 12 + Math.ceil(words.length / 2)),
    vocabIds,
    hskNum >= 5 ? ['read', 'write', 'listen'] : ['listen', 'read', 'speak'],
    `Bài ${index} — ${words.length} từ HSK ${hskNum}, chủ đề ${topicLabel}. Học từ, nghe và luyện nói theo hội thoại.`,
    makeDialogue(words)
  );
}

function buildLevel(levelId, wordsAll) {
  const meta = LEVEL_META[levelId];
  const hskNum = meta.num;
  const words = wordsAll.filter(w => w.hsk === hskNum);
  const usedIds = new Set();
  const lessons = [];

  (MANUAL[levelId] || []).forEach(row => {
    const L = manualToLesson(row);
    usedIds.add(L.id);
    lessons.push(L);
  });

  const manualHanzi = new Set(lessons.flatMap(l => l.vocabIds));
  const remaining = words.filter(w => !manualHanzi.has(w.hanzi));
  const chunk = meta.chunk;
  const groups = groupByTopic(remaining);
  const topicOrder = TOPICS.map(t => t.id);
  const counters = {};

  topicOrder.forEach(topicId => {
    const list = groups[topicId];
    if (!list?.length) return;
    for (let i = 0; i < list.length; i += chunk) {
      counters[topicId] = (counters[topicId] || 0) + 1;
      lessons.push(autoLesson(
        levelId, hskNum, counters[topicId],
        list.slice(i, i + chunk), usedIds, topicId
      ));
    }
  });

  Object.keys(groups).forEach(topicId => {
    if (!topicOrder.includes(topicId)) {
      const list = groups[topicId];
      for (let i = 0; i < list.length; i += chunk) {
        counters[topicId] = (counters[topicId] || 0) + 1;
        lessons.push(autoLesson(levelId, hskNum, counters[topicId], list.slice(i, i + chunk), usedIds, topicId));
      }
    }
  });

  while (lessons.length < meta.minLessons && words.length > 0) {
    const topicId = topicOrder[(lessons.length) % topicOrder.length];
    const slice = words.filter(w => w.topic === topicId).slice(0, 6);
    const pick = slice.length >= 4 ? slice : words.slice(0, 6);
    counters[topicId] = (counters[topicId] || 0) + 1;
    lessons.push(autoLesson(levelId, hskNum, counters[topicId], pick, usedIds, topicId));
  }

  return {
    id: levelId,
    name: meta.name,
    color: meta.color,
    description: meta.desc,
    totalLessons: lessons.length,
    lessons
  };
}

function loadVocab() {
  if (!fs.existsSync(VOCAB_PATH)) {
    console.warn('Chưa có vocabulary.json — chạy: npm run data:vocab');
    return [];
  }
  return JSON.parse(fs.readFileSync(VOCAB_PATH, 'utf8')).words || [];
}

const words = loadVocab();
const levels = Object.keys(LEVEL_META).map(id => buildLevel(id, words));

const topics = TOPICS.map(t => ({ ...t }));

topics.forEach(t => {
  t.lessonCount = levels.reduce((n, lv) =>
    n + lv.lessons.filter(x => x.topic === t.id).length, 0);
});

const out = { levels, topics };
const p = path.join(dataDir, 'lessons.json');
fs.writeFileSync(p, JSON.stringify(out, null, 2));

const total = levels.reduce((n, l) => n + l.lessons.length, 0);
console.log('OK', p, '—', total, 'bài');
levels.forEach(l => console.log(`  ${l.name}: ${l.lessons.length} bài`));
topics.filter(t => t.lessonCount > 0).forEach(t =>
  console.log(`  ${t.icon} ${t.name}: ${t.lessonCount} bài`));
