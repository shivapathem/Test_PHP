USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_GET_DutyHistory]    Script Date: 13/09/2021 16:02:18 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
DECLARE @strSQL NVARCHAR(max)

IF EXISTS (SELECT * FROM sys.objects WHERE object_id = OBJECT_ID(N'[dbo].[usp_GET_DutyHistory]') AND type in (N'P', N'PC'))
SET @strSQL = N'ALTER '
ELSE
SET @strSQL = N'CREATE '

--Execute the script statement
Set @strSQL= @strSQL + N' PROCEDURE [dbo].[usp_GET_DutyHistory]
@dutyId INT,
@history varchar(400)

AS
BEGIN
	SET NOCOUNT ON;
	--DECLARE @historytype int

	SELECT History FROM MasterDuties WHERE MasterDutyID = @dutyId and IsActive = 1 
						
END
'

EXEC dbo.sp_executesql @strSQL 

GO
