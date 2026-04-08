USE [Allocate7]
GO

/****** Object:  Table [dbo].[MasterDuties]    Script Date: 13/08/2021  ******/
SET ANSI_NULLS ON
GO

SET QUOTED_IDENTIFIER ON
GO

if exists(select Programme  from Programmes where (Programme = '' or Programme is null))
BEGIN 
	delete from Programmes where (Programme = '' or Programme is null)
END
GO