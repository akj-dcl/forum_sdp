<script setup lang="ts">
import { ref } from 'vue';

const emit = defineEmits<{
    (e: 'select-emoji', emoji: string): void;
    (e: 'select-gif', payload: { url: string; name: string }): void;
    (e: 'select-sticker', payload: { url: string; name: string }): void;
}>();

const activeTab = ref<'emoji' | 'gif' | 'sticker'>('emoji');

// Emojis categories data
const emojiCategories = [
    {
        name: 'Emotions',
        items: ['😂', '🤣', '😊', '😍', '🥰', '😘', '😜', '🤔', '😎', '😭', '😡', '😱', '🤫', '😴', '🥳', '🤯', '😇', '💩', '🤡', '👽']
    },
    {
        name: 'Gestures',
        items: ['👍', '👎', '👌', '✌️', '🤞', '🤝', '👏', '🙌', '🙋‍♂️', '🙋‍♀️', '🙏', '💪', '👋', '🔥']
    },
    {
        name: 'Hearts & Fun',
        items: ['❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍', '💖', '✨', '💥', '⭐', '💯', '⚠️', '🚨', '🎉', '🎁', '🎈', '🏆', '🚀']
    }
];

// Reaction GIFs list (public high-quality reactions)
const reactionGifs = [
    { name: 'Yay Clapping', url: 'https://media.giphy.com/media/l3q2Z6MX1LxAH8276/giphy.gif' },
    { name: 'Thumbs Up', url: 'https://media.giphy.com/media/XreQmk7ETCak0/giphy.gif' },
    { name: 'Success Jump', url: 'https://media.giphy.com/media/2g2fupMt4CrDV3t731/giphy.gif' },
    { name: 'Dance Happy', url: 'https://media.giphy.com/media/blSTtZehjQ9SNjN9OA/giphy.gif' },
    { name: 'Confused Shrug', url: 'https://media.giphy.com/media/3o7qE1YN7aBOFPRw8E/giphy.gif' },
    { name: 'Mindblown', url: 'https://media.giphy.com/media/Um3ljJl8jfqY759B4Y/giphy.gif' },
    { name: 'Shocked Face', url: 'https://media.giphy.com/media/3o72F8t9CEwZAVwu1W/giphy.gif' },
    { name: 'Laughing Out Loud', url: 'https://media.giphy.com/media/dtGIRL0FDp6yA/giphy.gif' }
];

// 3D Fluent Stickers (using beautiful Microsoft Fluent Emojis via GitHub raw CDN)
const fluentStickers = [
    { name: 'Coding Bear', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Animals/Bear.png' },
    { name: 'Thumbs Up 3D', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Hand%20gestures/Thumbs%20Up.png' },
    { name: 'Heart Ribbon', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Smilies/Heart%20with%20Ribbon.png' },
    { name: 'Exploding Head', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Smilies/Exploding%20Head.png' },
    { name: 'Star Struck', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Smilies/Star-Struck.png' },
    { name: 'Sunglasses Cool', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Smilies/Smiling%20Face%20with%20Sunglasses.png' },
    { name: 'Rocket Space', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Travel%20and%20Places/Rocket.png' },
    { name: 'Firecracker Fun', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Activities/Firecracker.png' },
    { name: 'Gold Trophy', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Activities/Trophy.png' },
    { name: 'Sparkles Magic', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Activities/Sparkler.png' },
    { name: 'Party Popper', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Activities/Party%20Popper.png' },
    { name: 'Hot Coffee', url: 'https://raw.githubusercontent.com/Tarikul-Islam-Anik/Animated-Fluent-Emojis/main/Emojis/Food%20Drink/Hot%20Beverage.png' }
];

const handleEmojiClick = (emoji: string) => {
    emit('select-emoji', emoji);
};

const handleGifClick = (gif: { url: string; name: string }) => {
    emit('select-gif', gif);
};

const handleStickerClick = (sticker: { url: string; name: string }) => {
    emit('select-sticker', sticker);
};
</script>

<template>
    <div class="w-80 h-96 flex flex-col bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl overflow-hidden z-50">
        <!-- Tab Navigation Header -->
        <div class="flex border-b border-slate-200 dark:border-slate-850 bg-slate-50 dark:bg-slate-950 shrink-0 select-none">
            <button
                @click="activeTab = 'emoji'"
                type="button"
                :class="[
                    'flex-1 py-3 text-xs font-bold transition-colors cursor-pointer border-none flex items-center justify-center gap-1.5 bg-transparent',
                    activeTab === 'emoji' ? 'text-primary dark:text-inverse-primary border-b-2 border-primary' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'
                ]"
            >
                <span class="material-symbols-outlined text-[16px]">sentiment_satisfied</span>
                Emoji
            </button>
            <button
                @click="activeTab = 'gif'"
                type="button"
                :class="[
                    'flex-1 py-3 text-xs font-bold transition-colors cursor-pointer border-none flex items-center justify-center gap-1.5 bg-transparent',
                    activeTab === 'gif' ? 'text-primary dark:text-inverse-primary border-b-2 border-primary' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'
                ]"
            >
                <span class="material-symbols-outlined text-[16px]">gif</span>
                GIF
            </button>
            <button
                @click="activeTab = 'sticker'"
                type="button"
                :class="[
                    'flex-1 py-3 text-xs font-bold transition-colors cursor-pointer border-none flex items-center justify-center gap-1.5 bg-transparent',
                    activeTab === 'sticker' ? 'text-primary dark:text-inverse-primary border-b-2 border-primary' : 'text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-900'
                ]"
            >
                <span class="material-symbols-outlined text-[16px]">sticky_note_2</span>
                Stiker
            </button>
        </div>

        <!-- Scrollable Content Area -->
        <div class="flex-1 overflow-y-auto p-4">
            <!-- Emojis Grid -->
            <div v-if="activeTab === 'emoji'" class="flex flex-col gap-4">
                <div v-for="category in emojiCategories" :key="category.name" class="flex flex-col gap-2">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider select-none">{{ category.name }}</span>
                    <div class="grid grid-cols-6 gap-2">
                        <button
                            v-for="emoji in category.items"
                            :key="emoji"
                            type="button"
                            @click="handleEmojiClick(emoji)"
                            class="aspect-square flex items-center justify-center text-xl hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg cursor-pointer border-none bg-transparent transition-colors p-1"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- GIFs Grid -->
            <div v-else-if="activeTab === 'gif'" class="grid grid-cols-2 gap-2">
                <button
                    v-for="gif in reactionGifs"
                    :key="gif.url"
                    type="button"
                    @click="handleGifClick(gif)"
                    class="group relative aspect-video w-full rounded-lg overflow-hidden border border-slate-200 dark:border-slate-800 hover:border-primary cursor-pointer p-0 bg-slate-950 flex items-center justify-center"
                >
                    <img :src="gif.url" :alt="gif.name" class="w-full h-full object-cover group-hover:scale-105 transition-transform" loading="lazy" />
                    <span class="absolute bottom-1 left-1.5 bg-black/60 text-[9px] text-white font-semibold py-0.5 px-1.5 rounded truncate max-w-[85%] select-none pointer-events-none">
                        {{ gif.name }}
                    </span>
                </button>
            </div>

            <!-- Stickers Grid -->
            <div v-else-if="activeTab === 'sticker'" class="grid grid-cols-3 gap-3">
                <button
                    v-for="sticker in fluentStickers"
                    :key="sticker.url"
                    type="button"
                    @click="handleStickerClick(sticker)"
                    class="group relative aspect-square p-2 rounded-xl border border-slate-200 dark:border-slate-800 hover:border-primary hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer bg-transparent transition-all flex items-center justify-center"
                    :title="sticker.name"
                >
                    <img :src="sticker.url" :alt="sticker.name" class="w-12 h-12 object-contain group-hover:scale-110 transition-transform" loading="lazy" />
                </button>
            </div>
        </div>
    </div>
</template>
