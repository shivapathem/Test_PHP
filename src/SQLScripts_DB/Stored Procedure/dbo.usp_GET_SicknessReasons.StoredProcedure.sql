USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_SicknessReasons]    Script Date: 13/09/2021 16:02:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GET_SicknessReasons]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_GET_SicknessReasons]


AS
BEGIN
	SET NOCOUNT ON;


Select SicknessReasonID,SicknessName from SicknessReason where IsActive =1
		order by SicknessName Asc				
END
'

EXEC dbo.sp_executesql @strSQL 

GO
