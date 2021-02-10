import { schema } from 'normalizr';

const avatar = new schema.Entity('avatars');

const user = new schema.Entity('users', { 
    avatar: avatar
}, {
    processStrategy: (value, parent) => {
        return { 
            ...value, 
            avatar: parent.avatar ? parent.avatar : null,
            hotList: parent.id
        };
    }
});

const hotListUserSchema = new schema.Entity('hotList', { 
    user: user, 
    avatar: avatar
});

export const hotListSchema = new schema.Array(hotListUserSchema);
