import { schema } from 'normalizr';

const avatar = new schema.Entity('avatars');

const user = new schema.Entity('users', { 
    avatar: avatar
}, {
    processStrategy: (value, parent) => {
        return { 
            ...value, 
            avatar: parent.avatar ? parent.avatar : null,
            conversation: parent.id
        };
    }
});

const conversationSchema = new schema.Entity('conversations', { 
    user: user, 
    avatar: avatar
});

export const conversationListSchema = new schema.Array(conversationSchema);
