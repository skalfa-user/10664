import { schema } from 'normalizr';

const avatar = new schema.Entity('avatars');
const user = new schema.Entity('users', {
    avatar: avatar,
}, {
    processStrategy: (value, parent, key) => {
        return {
            ...value,
            avatar: parent.avatar ? parent.avatar : null
        };
    }
});

const notificationSchema = new schema.Entity('notifications', {
    user: user, 
    avatar: avatar
});

export const notificationListSchema = new schema.Array(notificationSchema);
