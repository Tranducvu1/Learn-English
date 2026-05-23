/**
 * Quy tắc gán chủ đề cho từ vựng & bài học
 */
const TOPICS = [
  { id: 'giao-tiep', name: 'Giao tiếp hàng ngày', icon: '💬' },
  { id: 'phuong-tien', name: 'Phương tiện & Đi lại', icon: '🚗' },
  { id: 'an-uong', name: 'Ăn uống', icon: '🍜' },
  { id: 'mua-sam', name: 'Mua sắm', icon: '🛒' },
  { id: 'du-lich', name: 'Du lịch', icon: '✈️' },
  { id: 'suc-khoe', name: 'Sức khỏe', icon: '🏥' },
  { id: 'thoi-tiet', name: 'Thời tiết', icon: '🌤' },
  { id: 'gia-dinh', name: 'Gia đình', icon: '👨‍👩‍👧' },
  { id: 'nha-o', name: 'Nhà ở', icon: '🏠' },
  { id: 'quan-ao', name: 'Quần áo & Thời trang', icon: '👕' },
  { id: 'giao-duc', name: 'Giáo dục & Học tập', icon: '📖' },
  { id: 'cong-viec', name: 'Công việc', icon: '💼' },
  { id: 'the-thao', name: 'Thể thao', icon: '⚽' },
  { id: 'cong-nghe', name: 'Công nghệ', icon: '💻' },
  { id: 'am-nhac', name: 'Âm nhạc & Giải trí', icon: '🎵' },
  { id: 'thien-nhien', name: 'Thiên nhiên & Môi trường', icon: '🌿' },
  { id: 'van-hoa', name: 'Văn hóa & Lễ hội', icon: '🏮' },
  { id: 'kinh-te', name: 'Kinh tế & Tài chính', icon: '💰' },
  { id: 'co-ban', name: 'Từ cơ bản', icon: '📌' },
  { id: 'thi-hsk', name: 'Ôn thi HSK', icon: '📝' }
];

/** Từ khóa Hán → chủ đề (ưu tiên khớp dài trước) */
const RULES = [
  { topic: 'phuong-tien', keys: [
    '车', '汽车', '火车', '飞机', '机场', '地铁', '公交', '出租车', '自行车', '摩托车',
    '船', '票', '站', '路', '交通', '驾驶', '司机', '乘客', '出发', '到达', '导航', '高速', '加油'
  ]},
  { topic: 'an-uong', keys: [
    '吃', '喝', '饭', '菜', '茶', '咖啡', '酒', '肉', '鱼', '米', '面', '汤', '甜', '辣', '咸',
    '餐厅', '厨房', '饿', '饱', '味道', '菜单', '服务员', '早餐', '午餐', '晚餐', '水果', '蔬菜', '牛奶', '鸡蛋'
  ]},
  { topic: 'mua-sam', keys: [
    '买', '卖', '钱', '商店', '超市', '市场', '便宜', '贵', '打折', '价格', '付款', '现金', '卡', '发票', '商品', '包装', '退货'
  ]},
  { topic: 'du-lich', keys: [
    '旅游', '旅行', '酒店', '房间', '护照', '签证', '地图', '博物馆', '景点', '导游', '行李', '预订', '风景', '拍照', '假期', '海滩', '山'
  ]},
  { topic: 'suc-khoe', keys: [
    '医院', '医生', '护士', '病', '疼', '药', '健康', '感冒', '发烧', '检查', '治疗', '手术', '休息', '锻炼', '减肥', '营养', '牙齿', '眼睛'
  ]},
  { topic: 'thoi-tiet', keys: [
    '天气', '热', '冷', '暖', '凉', '下雨', '雪', '风', '太阳', '云', '季节', '春天', '夏天', '秋天', '冬天', '温度', '晴', '阴', '雾', '台风'
  ]},
  { topic: 'gia-dinh', keys: [
    '家', '妈妈', '爸爸', '父母', '孩子', '儿子', '女儿', '哥哥', '姐姐', '弟弟', '妹妹', '爷爷', '奶奶', '丈夫', '妻子', '结婚', '亲戚'
  ]},
  { topic: 'nha-o', keys: [
    '房子', '房间', '卧室', '厨房', '厕所', '楼', '层', '门', '窗', '床', '桌子', '椅子', '沙发', '灯', '钥匙', '租', '邻居', '搬家'
  ]},
  { topic: 'quan-ao', keys: [
    '衣服', '裤子', '鞋', '帽子', '裙子', '衬衫', '外套', '穿', '脱', '洗', '颜色', '尺寸', '时尚', '布料', '袜子', '手套'
  ]},
  { topic: 'giao-duc', keys: [
    '学校', '老师', '学生', '课', '考试', '学习', '大学', '中学', '小学', '作业', '成绩', '毕业', '专业', '图书馆', '教室', '笔记', '复习', '汉语'
  ]},
  { topic: 'cong-viec', keys: [
    '工作', '公司', '老板', '同事', '会议', '报告', '项目', '客户', '合同', '工资', '加班', '辞职', '面试', '简历', '经验', '职业', '办公室', '任务'
  ]},
  { topic: 'the-thao', keys: [
    '运动', '足球', '篮球', '游泳', '跑步', '比赛', '冠军', '锻炼', '健身房', '球', '队', '赢', '输', '教练', '运动员', '网球', '乒乓球'
  ]},
  { topic: 'cong-nghe', keys: [
    '电脑', '手机', '网络', '互联网', '软件', '程序', '数据', '密码', '屏幕', '键盘', '下载', '上传', '网站', '应用', '人工智能', '机器人', '电子', '科技'
  ]},
  { topic: 'am-nhac', keys: [
    '音乐', '歌', '唱', '电影', '电视', '演员', '导演', '节目', '游戏', '玩', '跳舞', '乐器', '钢琴', '吉他', '小说', '故事', '艺术', '画'
  ]},
  { topic: 'thien-nhien', keys: [
    '环境', '污染', '保护', '动物', '植物', '树', '花', '草', '森林', '河', '海', '湖', '山', '地球', '资源', '能源', '气候', '生态', '自然'
  ]},
  { topic: 'van-hoa', keys: [
    '文化', '传统', '节日', '春节', '历史', '古代', '现代', '礼貌', '习俗', '礼物', '祝福', '宗教', '哲学', '文学', '诗歌', '汉字'
  ]},
  { topic: 'kinh-te', keys: [
    '经济', '市场', '发展', '竞争', '投资', '银行', '贷款', '利息', '股票', '贸易', '出口', '进口', '税收', '财政', '消费', '生产', '利润', '商业'
  ]},
  { topic: 'giao-tiep', keys: [
    '你好', '谢谢', '再见', '请', '对不起', '没关系', '朋友', '见面', '聊天', '电话', '邮件', '消息', '介绍', '邀请', '同意', '拒绝', '帮助', '问题', '回答'
  ]},
  { topic: 'thi-hsk', keys: [
    'HSK', '考试', '语法', '词汇', '听力', '阅读', '写作', '口语', '综合', '证书', '真题', '模拟'
  ]}
];

const TOPIC_NAMES = Object.fromEntries(TOPICS.map(t => [t.id, t.name]));

function classifyTopic(hanzi, pinyin = '', meaning = '', hsk = 1) {
  const text = `${hanzi}${pinyin}${meaning}`.toLowerCase();
  for (const rule of RULES) {
    for (const key of rule.keys) {
      if (hanzi.includes(key) || hanzi === key) return rule.topic;
    }
  }
  const enTravel = /travel|airport|train|bus|car|drive|road|ticket|vehicle|transport/i;
  const enFood = /eat|drink|food|meal|restaurant|coffee|tea|cook/i;
  const enHealth = /health|hospital|doctor|medicine|sick|pain/i;
  const enWeather = /weather|rain|snow|hot|cold|wind|sun/i;
  if (enTravel.test(meaning)) return 'phuong-tien';
  if (enFood.test(meaning)) return 'an-uong';
  if (enHealth.test(meaning)) return 'suc-khoe';
  if (enWeather.test(meaning)) return 'thoi-tiet';

  const rotate = [
    'giao-tiep', 'phuong-tien', 'an-uong', 'mua-sam', 'du-lich', 'suc-khoe',
    'thoi-tiet', 'gia-dinh', 'nha-o', 'giao-duc', 'the-thao', 'am-nhac', 'thien-nhien'
  ];
  const code = [...hanzi].reduce((s, c) => s + c.charCodeAt(0), 0);
  const pick = rotate[(code + hsk) % rotate.length];

  const fallback = { 1: 'giao-tiep', 2: 'giao-tiep', 3: 'du-lich', 4: 'cong-viec', 5: 'van-hoa', 6: 'thi-hsk' };
  if (hsk <= 2) return pick;
  return fallback[hsk] || pick;
}

function dominantTopic(words) {
  const count = {};
  words.forEach(w => {
    const t = w.topic || 'co-ban';
    count[t] = (count[t] || 0) + 1;
  });
  return Object.entries(count).sort((a, b) => b[1] - a[1])[0]?.[0] || 'co-ban';
}

function groupByTopic(words) {
  const groups = {};
  words.forEach(w => {
    const t = w.topic || 'co-ban';
    if (!groups[t]) groups[t] = [];
    groups[t].push(w);
  });
  return groups;
}

module.exports = { TOPICS, TOPIC_NAMES, RULES, classifyTopic, dominantTopic, groupByTopic };
