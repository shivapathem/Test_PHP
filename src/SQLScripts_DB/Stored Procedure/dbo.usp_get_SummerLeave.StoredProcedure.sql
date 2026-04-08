USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_get_SummerLeave]    Script Date: 28/10/2022 16:09:29 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_get_SummerLeave]
@TodayDate varchar(100),
@NextYear varchar(100),
@isAdmin  INT,
@NetLogin varchar(100)

AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;

		DECLARE	@query  AS NVARCHAR(MAX),
			    @conditionString AS NVARCHAR(MAX)

	IF(@isAdmin = 0)
			SET @conditionString = 'AND  (ISNULL(SW.Admin, 0) = 0)'
		ELSE
			SET @conditionString = 'AND (ISNULL(SW.Admin, 0) >= 1)'
 
 SET @query ='SELECT   SL.dStart, SL.dEnd,SL.amount,LT.ID AS TypeID, SW.EFTSummer
               FROM         SummerLeave SL (nolock)
               INNER JOIN   leave_types LT (nolock) ON SL.GroupID = LT.GroupID 
               INNER JOIN   Staff_Web_Config_LeaveGroups_Link SW (nolock) ON SL.GroupID = SW.LeaveGroupID
               WHERE        (SL.dUntil >= CONVERT(DATETIME, '''+@TodayDate+''', 102)) 
               AND          (SL.dEnd <= CONVERT(DATETIME, '''+@NextYear+''', 102)) 
               AND          (SL.dEnd >= CONVERT(DATETIME, '''+@TodayDate+''', 102))'+@conditionString+'
               AND          (SW.Login = '''+@NetLogin+''') and (SW.IsActive=1)';

			   exec sp_executesql @query
END
