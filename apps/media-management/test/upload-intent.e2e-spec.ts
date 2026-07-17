import { Test, TestingModule } from '@nestjs/testing';
import { INestApplication, ValidationPipe } from '@nestjs/common';
import request from 'supertest';
import { App } from 'supertest/types';
import { MediaModule } from '../src/media/media.module';
import { PrismaService } from '../src/media/infrastructure/PrismaService';

jest.setTimeout(30000);

describe('UploadIntentController (e2e)', () => {
  let app: INestApplication<App>;
  let prisma: PrismaService;
  const createdTokens: string[] = [];

  const validBody = () => ({
    entityGid: 'gid://catalog/Brand/123',
    routingKey: 'colorlab.brand.media-uploaded',
    userId: '3fa85f64-5717-4562-b3fc-2c963f66afa6',
    variants: ['thumbnail', 'medium'],
    ttlSeconds: 600,
  });

  beforeAll(async () => {
    const moduleFixture: TestingModule = await Test.createTestingModule({
      imports: [MediaModule],
    }).compile();

    app = moduleFixture.createNestApplication();
    app.useGlobalPipes(
      new ValidationPipe({
        whitelist: true,
        forbidNonWhitelisted: true,
        transform: true,
      }),
    );
    await app.init();
    prisma = app.get(PrismaService);
  });

  afterAll(async () => {
    if (createdTokens.length > 0) {
      await prisma.uploadIntent.deleteMany({
        where: { token: { in: createdTokens } },
      });
    }
    await app.close();
  });

  it('creates an upload intent and returns a public upload URL', async () => {
    const response = await request(app.getHttpServer())
      .post('/internal/media/generate-upload-url')
      .send(validBody())
      .expect(200);

    expect(response.body).toHaveProperty('uploadUrl');
    const match = (response.body as { uploadUrl: string }).uploadUrl.match(
      /^\/api\/media\/upload\/([0-9a-f-]{36})$/,
    );
    expect(match).not.toBeNull();

    const token = match![1];
    createdTokens.push(token);

    const stored = await prisma.uploadIntent.findUnique({ where: { token } });
    expect(stored).not.toBeNull();
    expect(stored?.entityGid).toBe('gid://catalog/Brand/123');
    expect(stored?.routingKey).toBe('colorlab.brand.media-uploaded');
    expect(stored?.userId).toBe('3fa85f64-5717-4562-b3fc-2c963f66afa6');
    expect(stored?.consumedAt).toBeNull();
    expect(stored?.expiresAt.getTime()).toBeGreaterThan(Date.now());
  });

  it('rejects a malformed entityGid', () => {
    return request(app.getHttpServer())
      .post('/internal/media/generate-upload-url')
      .send({ ...validBody(), entityGid: 'not-a-gid' })
      .expect(400);
  });

  it('rejects an unknown variant name', () => {
    return request(app.getHttpServer())
      .post('/internal/media/generate-upload-url')
      .send({ ...validBody(), variants: ['not-a-real-variant'] })
      .expect(400);
  });

  it('rejects an empty variants array', () => {
    return request(app.getHttpServer())
      .post('/internal/media/generate-upload-url')
      .send({ ...validBody(), variants: [] })
      .expect(400);
  });

  it('rejects ttlSeconds above the 1 hour ceiling', () => {
    return request(app.getHttpServer())
      .post('/internal/media/generate-upload-url')
      .send({ ...validBody(), ttlSeconds: 3601 })
      .expect(400);
  });

  it('rejects unexpected extra top-level fields', () => {
    return request(app.getHttpServer())
      .post('/internal/media/generate-upload-url')
      .send({ ...validBody(), extraField: 'nope' })
      .expect(400);
  });
});
