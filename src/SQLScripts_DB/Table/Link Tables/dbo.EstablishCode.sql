USE [Allocate7]
GO

/****** Object:  Table [dbo].[EstablishCode]    Script Date: 28/12/2021 21:51:26 ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO
IF  NOT EXISTS (SELECT 1 FROM sys.objects 
WHERE object_id = OBJECT_ID(N'[dbo].[EstablishCode]'))

BEGIN

CREATE TABLE [dbo].[EstablishCode](
	[EstablishCodeId] [int] IDENTITY(1,1) NOT NULL,
	[EstablishCode] [varchar](50) NULL,
	[EstablishCodeDescription] [varchar](75) NULL,
	[CreatedDate] [datetime] NULL,
	[CreatedBy] [int] NULL,
	[UpdatedDate] [datetime] NULL,
	[UpdatedBy] [int] NULL,
 CONSTRAINT [PK_EstablishCode] PRIMARY KEY CLUSTERED 
(
	[EstablishCodeId] ASC
)WITH (PAD_INDEX = OFF, STATISTICS_NORECOMPUTE = OFF, IGNORE_DUP_KEY = OFF, ALLOW_ROW_LOCKS = ON, ALLOW_PAGE_LOCKS = ON) ON [PRIMARY]
) ON [PRIMARY]
END
GO

