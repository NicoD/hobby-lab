-- CreateTable
CREATE TABLE "UploadIntent" (
    "id" TEXT NOT NULL,
    "token" TEXT NOT NULL,
    "entityGid" TEXT NOT NULL,
    "userId" TEXT NOT NULL,
    "routingKey" TEXT NOT NULL,
    "constraints" JSONB NOT NULL,
    "expiresAt" TIMESTAMP(3) NOT NULL,
    "consumedAt" TIMESTAMP(3),
    "createdAt" TIMESTAMP(3) NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT "UploadIntent_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "UploadIntent_token_key" ON "UploadIntent"("token");
